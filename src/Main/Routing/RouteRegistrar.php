<?php

namespace Src\Main\Routing;

use BadMethodCallException;
use InvalidArgumentException;

class RouteRegistrar
{
    protected array $attributes = [];
    protected array $allowedAttributes = ['controller', 'middleware', 'name', 'prefix'];
    protected array $methods = ["get", "post", "put", "delete"];
    public function __construct(
        protected Router $router,
    ) {}
    public function group(string $file): static
    {
        $this->router->group($this->attributes, fn() => require $file);

        return $this;
    }
    public function addAttribute(string $name, array $params): static
    {
        if (!in_array($name, $this->allowedAttributes)) {
            throw new InvalidArgumentException("Attribute '{$name}' is not allowed.");
        }

        $this->attributes[$name] = $name === "middleware" ? $params : $params[0];

        return $this;
    }
    protected function registerRoute(string $method, string $uri, $action = null)
    {
        if (! is_array($action)) {
            $action = array_merge($this->attributes, $action ? ['uses' => $action] : []);
        }

        return $this->router->{$method}($uri, $this->compileAction($action));
    }
    public function __call(string $method, array $parameters)
    {
        if (in_array($method, $this->methods)) {
            return $this->registerRoute($method, ...$parameters);
        }

        if (in_array($method, $this->allowedAttributes)) {
            return $this->addAttribute($method, $parameters);
        }

        $class = static::class;

        throw new BadMethodCallException("Method {$class}::{$method} does not exist.");
    }
}
