<?php

namespace Src\Symfony\Http;

class RequestInput implements IRequestInput
{
    public function __construct(
        protected array $items = []
    ) {}
    public function all(): array
    {
        return $this->items;
    }
    public function get(string $key, string $default = null): ?string
    {
        return $this->items[$key] ?? $default;
    }
    public function set(string $key, ?string $value): void
    {
        unset($this->items[$key]);
        $this->add($key, $value);
    }
    public function add(string $key, ?string $value): void
    {
        $this->items[$key] = $value;
    }
    public function has(string $key): bool
    {
        return isset($this->items[$key]);
    }
    public function remove(string $key): void
    {
        unset($this->items[$key]);
    }
}
