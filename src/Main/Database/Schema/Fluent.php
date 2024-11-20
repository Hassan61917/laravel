<?php

namespace Src\Main\Database\Schema;

use ArrayAccess;

class Fluent implements ArrayAccess
{
    public function __construct(
        protected array $attributes = []
    ) {}
    public function get(string $key, string $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
    public function value(string $key, string $default = null): mixed
    {
        if ($this->exists($key)) {
            return $this->attributes[$key];
        }

        return $default;
    }
    public function scope(string $key, string $default = null): static
    {
        return new static(
            (array) $this->get($key, $default)
        );
    }
    public function exists(string $key): bool
    {
        return isset($this->attributes[$key]);
    }
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    public function toArray(): array
    {
        return $this->attributes;
    }
    public function toJson(int $options = 0): string
    {
        return json_encode($this->attributes, $options);
    }
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }
    public function offsetGet(mixed $offset): string
    {
        return $this->value($offset);
    }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = $value;
    }
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }
    public function __call(string $method, array $parameters): static
    {
        $this->attributes[$method] = count($parameters) > 0 ? reset($parameters) : true;

        return $this;
    }
    public function __get(string $key)
    {
        return $this->value($key);
    }
    public function __set(string $key, string $value)
    {
        $this->offsetSet($key, $value);
    }
    public function __isset(string $key)
    {
        return $this->offsetExists($key);
    }
    public function __unset(string $key)
    {
        $this->offsetUnset($key);
    }
}
