<?php

namespace Src\Main\Cache;

use Closure;
use Src\Main\Cache\Services\ICacheStore;

interface ICacheRepository
{
    public function get(string $key, mixed $default = null): mixed;
    public function clear(): void;
    public function has(string $key): bool;
    public function pull(string $key): mixed;
    public function put(string $key, mixed $value, int $ttl = -1): mixed;
    public function increment(string $key, int $number = 1): void;
    public function decrement(string $key, int $number = 1): void;
    public function forever(string $key, mixed $value): void;
    public function remember(string $key, int $ttl, Closure $callback);
    public function rememberForever(string $key, Closure $callback): mixed;
    public function forget(string $key): void;
    public function getStore(): ICacheStore;
}
