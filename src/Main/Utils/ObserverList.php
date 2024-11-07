<?php

namespace Src\Main\Utils;

use Closure;

class ObserverList implements IObserverList
{
    protected array $callbacks = [];
    public function count(): int
    {
        return count($this->callbacks);
    }
    public function add(Closure $closure): void
    {
        $this->callbacks[] = $closure;
    }
    public function append(string $key, Closure $closure): void
    {
        if (!isset($this->callbacks[$key])) {
            $this->callbacks[$key] = [];
        }

        $this->callbacks[$key][] = $closure;
    }
    public function getAll(string $key = null): array
    {
        return $key ? $this->callbacks[$key] ?? [] : $this->callbacks;
    }
    public function run(array $args = [], string $key = null): void
    {
        foreach ($this->getAll($key) as $callback) {
            $callback(...$args);
        }
    }
    public function reset(): void
    {
        $this->callbacks = [];
    }
}
