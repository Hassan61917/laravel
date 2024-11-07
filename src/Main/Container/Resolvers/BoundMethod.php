<?php

namespace Src\Main\Container\Resolvers;

use ReflectionMethod;

trait BoundMethod
{
    protected function call(object $instance, ?string $method, array $parameters = []): mixed
    {
        if (is_null($method)) {
            $method = "__invoke";
        }
        $reflector = new ReflectionMethod($instance, $method);
        $parameters = $this->addDependencies($reflector, $parameters);
        return $instance->{$method}(...$parameters);
    }
    protected function addDependencies(ReflectionMethod $method, array $parameters): array
    {
        $dependencies = $this->getDependencies($method);
        $result = [];
        foreach ($dependencies as $index => $dependency) {
            $name = $dependency->getName();
            if (array_key_exists($name, $parameters)) {
                $result[$name] = $parameters[$name];
            } elseif (isset($parameters[$index])) {
                $result[$name] = $parameters[$index];
            } else {
                $result[$name] = $this->resolveDependency($dependency);
            }
        }
        return $result;
    }
}
