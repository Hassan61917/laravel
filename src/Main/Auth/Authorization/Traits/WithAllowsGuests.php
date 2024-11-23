<?php

namespace Src\Main\Auth\Authorization\Traits;

use Exception;
use ReflectionClass;
use ReflectionFunction;
use ReflectionParameter;

trait WithAllowsGuests
{
    protected function methodAllowsGuests(string $class, string $method): bool
    {
        try {
            $reflection = new ReflectionClass($class);

            $method = $reflection->getMethod($method);

            $parameters = $method->getParameters();

            return isset($parameters[0]) && $this->parameterAllowsGuests($parameters[0]);
        } catch (Exception) {
            return false;
        }
    }
    protected function callbackAllowsGuests(callable $callback): bool
    {
        try {
            $parameters = (new ReflectionFunction($callback))->getParameters();

            return isset($parameters[0]) && $this->parameterAllowsGuests($parameters[0]);
        } catch (Exception) {
            return false;
        }
    }
    protected function parameterAllowsGuests(ReflectionParameter $parameter): bool
    {
        return ($parameter->hasType() && $parameter->allowsNull()) ||
            ($parameter->isDefaultValueAvailable() && is_null($parameter->getDefaultValue()));
    }
}
