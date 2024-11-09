<?php

namespace Src\Symfony\Http;

interface IRequestInput
{
    public function all(): array;
    public function get(string $key, string $default = null): ?string;
    public function set(string $key, ?string $value): void;
    public function add(string $key, string $value): void;
    public function has(string $key): bool;
    public function remove(string $key): void;
}
