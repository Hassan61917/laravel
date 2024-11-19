<?php

namespace Src\Main\Support\Traits;

use Exception;
use InvalidArgumentException;

trait ForwardsCalls
{
    protected function forwardCallTo(object $object, string $method, array $parameters): mixed
    {
        try {
            if (!method_exists($object, $method)) {
                $class = get_class($object);
                throw new Exception("Call to undefined method $method on class $class");
            }
            return $object->{$method}(...$parameters);
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }
    protected function forwardDecoratedCallTo(object $object, string $method, array $parameters): mixed
    {
        $result = $this->forwardCallTo($object, $method, $parameters);

        return $result === $object ? $this : $result;
    }
}
