<?php

namespace Src\Main\Cache\Services;

interface ICacheStore
{
    public function get(string $key): mixed;
    public function put(string $key, mixed $value, int $seconds): mixed;
    public function increment(int $key, int $number = 1): void;
    public function decrement(string $key, int $number = 1): void;
    public function forever(string $key, mixed $value): void;
    public function forget(string $key): void;
    public function flush(): void;
    public function getPrefix(): string;
}
