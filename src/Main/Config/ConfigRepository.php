<?php

namespace Src\Main\Config;

use ArrayAccess;
use Illuminate\Support\Arr;
use InvalidArgumentException;

class ConfigRepository implements IConfigRepository, ArrayAccess
{
    public function __construct(
        protected array $items = []
    ) {}
    public function set(string $key, mixed $value = null): void
    {
        Arr::set($this->items, $key, $value);
    }
    public function has(string $key): bool
    {
        return Arr::has($this->items, $key);
    }
    public function get(string $key): mixed
    {
        return Arr::get($this->items, $key);
    }
    public function push(string $key, mixed $value = null): void
    {
        $result = $this->get($key);

        $result[] = $value;

        $this->set($key, $result);
    }
    public function getMany(string ...$keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }
    public function isarray(string $key): array
    {
        $value = $this->get($key);

        if (! is_array($value)) {
            throw new InvalidArgumentException("Configuration value for key {$key} must be an array");
        }

        return $value;
    }
    public function all(): array
    {
        return $this->items;
    }
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }
    public function offsetUnset(mixed $offset): void
    {
        $this->set($offset, null);
    }
}
