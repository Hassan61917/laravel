<?php

namespace Src\Main\Container;

use Closure;

interface IContainer
{
    public function alias(string $abstract, string ...$aliases): void;
    public function getAlias(string $abstract): string;
    public function bind(string $abstract, string|Closure $concrete = null, bool $shared = false): void;
    public function singleton(string $abstract, string|Closure $concrete = null): void;
    public function instance(string $abstract, mixed $instance): mixed;
    public function bound(string $abstract): bool;
    public function beforeResolving(string|Closure $abstract, ?Closure $callback = null): void;
    public function resolving(string|Closure $abstract, ?Closure $callback = null): void;
    public function afterResolving(string|Closure $abstract, ?Closure $callback = null): void;
    public function make(string $abstract): mixed;
    public function resolved(string $abstract): bool;
    public function call(array $items, array $parameters = []): mixed;
    public function flush(): void;
}
