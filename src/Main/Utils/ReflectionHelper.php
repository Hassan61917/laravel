<?php

namespace Src\Main\Utils;

use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class ReflectionHelper
{
    public static function getClassName(ReflectionParameter $parameter): string
    {
        $name = $parameter->getType()->getName();

        $class = $parameter->getDeclaringClass();

        if (!is_null($class)) {
            if ($name === 'self') {
                return $class->getName();
            }
            if ($name === 'parent' && $parent = $class->getParentClass()) {
                return $parent->getName();
            }
        }

        return $name;
    }
    public static function isPrimitive(ReflectionParameter $parameter): bool
    {
        return $parameter->getType()->isBuiltin();
    }
    public static function isParameterSubclassOf(ReflectionParameter $parameter, string $className): bool
    {
        $paramClassName = static::getClassName($parameter);

        return $paramClassName
            && (class_exists($paramClassName) || interface_exists($paramClassName))
            && (new ReflectionClass($paramClassName))->isSubclassOf($className);
    }
    public static function isCallable(array $var): bool
    {
        if (count($var) != 2) {
            return false;
        }

        $class = is_object($var[0]) ? get_class($var[0]) : $var[0];

        $method = $var[1];

        if (! class_exists($class)) {
            return false;
        }

        if (method_exists($class, $method)) {
            return (new ReflectionMethod($class, $method))->isPublic();
        }

        if (is_object($var[0]) && method_exists($class, '__call')) {
            return (new ReflectionMethod($class, '__call'))->isPublic();
        }

        if (! is_object($var[0]) && method_exists($class, '__callStatic')) {
            return (new ReflectionMethod($class, '__callStatic'))->isPublic();
        }

        return false;
    }

    public static function getParameterClassName(ReflectionParameter $parameter): ?string
    {
        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }

        return static::getClassName($parameter);
    }
}
