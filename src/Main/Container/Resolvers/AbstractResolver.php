<?php

namespace Src\Main\Container\Resolvers;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use Src\Main\Container\IContainer;
use Src\Main\Utils\ReflectionHelper;

class AbstractResolver implements IAbstractResolver
{
    use BoundMethod;
    protected array $buildStack = [];
    public function __construct(
        protected IContainer $container,
    ) {}
    public function buildClass(string $abstract): object
    {
        $reflector = $this->getReflector($abstract);

        $this->buildStack[] = $abstract;

        $dependencies = $this->getDependencies($reflector->getConstructor());

        $instances = $this->getInstances($dependencies);

        array_pop($this->buildStack);

        return $reflector->newInstance(...$instances);
    }
    public function buildMethod(object|string $instance, ?string $method, array $parameters): mixed
    {
        if (is_string($instance)) {
            $instance = $this->buildClass($instance);
        }

        return $this->call($instance, $method, $parameters);
    }
    protected function getReflector(string $abstract): ReflectionClass
    {
        try {
            $reflector = new ReflectionClass($abstract);
            if (!$reflector->isInstantiable()) {
                $this->notInstantiable($abstract);
            }
            return $reflector;
        } catch (Exception $e) {
            throw new Exception("class $abstract does not exist.");
        }
    }
    protected function notInstantiable(string $concrete)
    {
        if (empty($this->buildStack)) {
            $message = "$concrete is not instantiable.";
        } else {
            $previous = implode(', ', $this->buildStack);

            $message = "Target $concrete is not instantiable while building $previous.";
        }

        throw new Exception($message);
    }
    protected function getDependencies(?ReflectionMethod $method): array
    {
        if (is_null($method)) {
            array_pop($this->buildStack);
            return [];
        }

        return $method->getParameters();
    }
    protected function getInstances(array $dependencies): array
    {
        try {
            return $this->resolveDependencies($dependencies);
        } catch (Exception $e) {
            array_pop($this->buildStack);
            throw $e;
        }
    }
    protected function resolveDependencies(array $dependencies): array
    {
        $results = [];
        foreach ($dependencies as $dependency) {
            $results[] = $this->resolveDependency($dependency);;
        }
        return $results;
    }
    protected function resolveDependency(mixed $dependency): mixed
    {
        return ReflectionHelper::isPrimitive($dependency)
            ? $this->resolvePrimitive($dependency)
            : $this->resolveClass($dependency);
    }
    protected function resolvePrimitive(ReflectionParameter $parameter): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->isVariadic()) {
            return [];
        }

        $message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

        throw new Exception($message);
    }
    protected function resolveClass(ReflectionParameter $parameter): mixed
    {
        try {
            return $this->container->make(ReflectionHelper::getClassName($parameter));
        } catch (Exception $e) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }
            if ($parameter->isVariadic()) {
                return [];
            }
            throw $e;
        }
    }
}
