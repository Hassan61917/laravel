<?php

namespace Src\Main\Cache\Services\Array;

use Carbon\Carbon;
use Src\Main\Cache\Services\ICacheStore;

class ArrayStore implements ICacheStore
{
    protected bool $serialize;
    protected array $storage = [];
    public function __construct(
        protected array $config = [],
    ) {
        $this->serialize = (bool)$this->config['serialize'] ?? false;
    }
    public function put(string $key, mixed $value, int $seconds): mixed
    {
        $this->storage[$key] = [
            'value' => $this->serialize ? serialize($value) : $value,
            'expiresAt' => $this->calculateExpiration($seconds),
        ];

        return $value;
    }
    public function forever(string $key, mixed $value): void
    {
        $this->put($key, $value, 0);
    }
    public function get(string $key): mixed
    {
        if (! $this->hasKey($key)) {
            return null;
        }

        $item = $this->storage[$key];

        $expiresAt = $item['expiresAt'] ?? 0;

        $value = $item['value'] ?? null;

        if ($expiresAt > 0 && $this->toCarbon() >= $expiresAt) {
            $this->forget($key);

            return null;
        }

        return $this->serialize ? unserialize($value) : $value;
    }
    public function increment(int $key, int $number = 1): void
    {
        $value = $this->get($key);

        if ($value && is_int($value)) {
            $value += $number;

            $value = $this->serialize ? serialize($value) : $value;

            $this->storage[$key]['value'] = $value;
        }
    }
    public function decrement(string $key, int $number = 1): void
    {
        $this->increment($key, $number * -1);
    }
    public function forget(string $key): void
    {
        if ($this->hasKey($key)) {
            unset($this->storage[$key]);
        }
    }
    public function flush(): void
    {
        $this->storage = [];
    }
    public function getPrefix(): string
    {
        return '';
    }
    protected function getItem(string $key): mixed
    {
        return $this->storage[$key];
    }
    protected function hasKey(string $key): bool
    {
        return array_key_exists($key, $this->storage);
    }
    protected function calculateExpiration(int $seconds): float
    {
        return $this->toTimestamp($seconds);
    }
    protected function toTimestamp(int $seconds): float
    {
        return $seconds > 0 ? $this->toCarbon() + $seconds : 0;
    }
    protected function toCarbon(): int
    {
        return Carbon::now()->getPreciseTimestamp(3) / 1000;
    }
}
