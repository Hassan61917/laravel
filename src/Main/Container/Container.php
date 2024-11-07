<?php

namespace Src\Main\Container;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use Src\Main\Container\Resolvers\AbstractResolver;
use Src\Main\Container\Resolvers\IAbstractResolver;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;

class Container implements IContainer, ArrayAccess
{
    protected static Container $instance;
    protected array $abstractAliases = [];
    protected array $aliases = [];
    protected array $instances = [];
    protected array $bindings = [];
    protected array $resolved = [];
    protected IObserverList $beforeResolving;
    protected IObserverList $resolving;
    protected IObserverList $afterResolving;
    protected IAbstractResolver $resolver;
    public function __construct()
    {
        $this->beforeResolving = new ObserverList();
        $this->resolving = new ObserverList();
        $this->afterResolving = new ObserverList();
        $this->resolver = new AbstractResolver($this);
    }
    public static function getInstance(): static
    {
        if (is_null(static::$instance)) {
            self::setInstance(new static());
        }

        return static::$instance;
    }
    public static function setInstance(Container $container = null): static
    {
        return static::$instance = $container;
    }
    public function alias(string $abstract, string ...$aliases): void
    {
        if (in_array($abstract, $aliases)) {
            throw new InvalidArgumentException("abstract cannot assign to it self");
        }
        foreach ($aliases as $alias) {
            $this->aliases[$alias] = $abstract;
            $this->abstractAliases[$abstract][] = $alias;
        }
    }
    public function getAlias(string $abstract): string
    {
        if ($this->isAlias($abstract)) {
            return $this->getAlias($this->aliases[$abstract]);
        }
        return $abstract;
    }
    public function bind(string $abstract, string|Closure $concrete = null, bool $shared = false): void
    {
        $this->removeOldInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (is_string($concrete)) {
            $concrete = $this->toClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact("concrete", "shared");
    }
    public function singleton(string $abstract, string|Closure $concrete = null): void
    {
        $this->bind($abstract, $concrete, true);
    }
    public function instance(string $abstract, mixed $instance): mixed
    {
        $this->removeAliases($abstract);

        $this->instances[$abstract] = $instance;

        return $instance;
    }
    public function bound(string $abstract): bool
    {
        return $this->isAlias($abstract) || $this->hasBound($abstract) || $this->hasInstance($abstract);
    }
    public function beforeResolving(string|Closure $abstract, ?Closure $callback = null): void
    {
        $this->addResolveCallback($abstract, $callback, $this->beforeResolving);
    }
    public function resolving(string|Closure $abstract, ?Closure $callback = null): void
    {
        $this->addResolveCallback($abstract, $callback, $this->resolving);
    }
    public function afterResolving(string|Closure $abstract, ?Closure $callback = null): void
    {
        $this->addResolveCallback($abstract, $callback, $this->afterResolving);
    }
    public function make(string $abstract): mixed
    {
        return $this->resolve($abstract);
    }
    public function resolved(string $abstract): bool
    {
        if ($this->isAlias($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        return isset($this->resolved[$abstract]) || $this->hasInstance($abstract);
    }
    public function call(array $items, array $parameters = []): mixed
    {
        return $this->resolver->buildMethod($items[0], $items[1] ?? null, $parameters);
    }
    public function forgetInstances(): void
    {
        $this->instances = [];
    }
    public function getBindings(): array
    {
        return $this->bindings;
    }
    public function flush(): void
    {
        $this->aliases = [];
        $this->resolved = [];
        $this->bindings = [];
        $this->instances = [];
        $this->abstractAliases = [];
    }
    protected function isAlias(string $abstract): bool
    {
        return isset($this->aliases[$abstract]);
    }
    protected function removeOldInstances(string $abstract): void
    {
        $this->removeInstance($abstract);

        $this->removeAlias($abstract);
    }
    protected function removeInstance(string $abstract): void
    {
        unset($this->instances[$abstract]);
    }
    protected function removeAlias(string $abstract): void
    {
        unset($this->aliases[$abstract]);
    }
    protected function toClosure(string $abstract, string $concrete): Closure
    {
        return fn(Container $container) => $container->resolveAbstract($abstract, $concrete);
    }
    protected function removeAliases(string $abstract): void
    {
        if (!$this->isAlias($abstract)) {
            return;
        }

        $this->removeAbstractAlias($abstract);

        $this->removeAlias($abstract);
    }
    protected function removeAbstractAlias(string $abstract): void
    {
        foreach ($this->abstractAliases as $class => $aliases) {
            foreach ($aliases as $offset => $alias) {
                if ($alias == $abstract) {
                    unset($this->abstractAliases[$class][$offset]);
                }
            }
        }
    }
    protected function hasBound(string $abstract): bool
    {
        return isset($this->bindings[$abstract]);
    }
    protected function hasInstance(string $abstract): bool
    {
        return isset($this->instances[$abstract]);
    }
    protected function addResolveCallback(string|Closure $abstract, ?Closure $callback, IObserverList $observer): void
    {
        if (is_string($abstract)) {
            $abstract = $this->getAlias($abstract);
        }

        if ($abstract instanceof Closure) {
            $callback = $abstract;
            $abstract = "global";
        }

        $observer->append($abstract, $callback);
    }
    protected function resolve(string $abstract): mixed
    {
        $abstract = $this->getAlias($abstract);

        $this->fireCallbackArray($abstract, [$abstract], $this->beforeResolving);

        if ($this->hasInstance($abstract)) {
            return $this->instances[$abstract];
        }

        $concrete = $this->getConcrete($abstract);

        $result = $this->resolveAbstract($abstract, $concrete);

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $result;
        }

        $this->fireCallbackArray($abstract, [$result], $this->resolving);

        $this->setResolved($abstract);

        $this->fireCallbackArray($abstract, [$result], $this->afterResolving);

        return $result;
    }
    protected function fireCallbackArray(string $abstract, array $args, IObserverList $observer): void
    {
        $args[] = $this;
        $observer->run($args, "global");
        $observer->run($args, $abstract);
    }
    protected function getConcrete(string $abstract): string|Closure
    {
        if ($this->hasBound($abstract)) {
            return $this->bindings[$abstract]["concrete"];
        }
        return $abstract;
    }
    protected function resolveAbstract(string $abstract, string|Closure $concrete): object
    {
        return $this->isBuildable($abstract, $concrete)
            ? $this->build($concrete)
            : $this->make($concrete);
    }
    protected function isBuildable(string $abstract, string|Closure $concrete): bool
    {
        return $abstract === $concrete || $concrete instanceof Closure;
    }
    protected function build(string|Closure $abstract): object
    {
        if ($abstract instanceof Closure) {
            return $abstract($this);
        }
        return $this->resolver->buildClass($abstract);
    }
    protected function isShared(string $abstract): bool
    {
        return $this->hasInstance($abstract) ||
            $this->hasBound($abstract) && $this->bindings[$abstract]["shared"] === true;
    }
    protected function setResolved(string $abstract): void
    {
        $this->resolved[$abstract] = true;
    }
    public function offsetExists(mixed $offset): bool
    {
        return $this->bound($offset);
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->make($offset);
    }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->bind($offset, fn() => $value);
    }
    public function offsetUnset(mixed $offset): void
    {
        unset($this->bindings[$offset], $this->instances[$offset], $this->resolved[$offset]);
    }
    public function __get($offset)
    {
        return $this[$offset];
    }
    public function __set(string $offset, string|Closure $value)
    {
        $this[$offset] = $value;
    }
}
