<?php

namespace Src\Main\Config;

interface IConfigRepository
{
    public function has(string $key): bool;
    public function get(string $key): mixed;
    public function set(string $key, mixed $value = null): void;
    public function push(string $key, mixed $value = null): void;
    public function all(): array;
}
