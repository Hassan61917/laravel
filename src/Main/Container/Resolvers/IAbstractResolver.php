<?php

namespace Src\Main\Container\Resolvers;

interface IAbstractResolver
{
    public function buildClass(string $abstract): object;
    public function buildMethod(string|object $instance, string $method, array $parameters): mixed;
}
