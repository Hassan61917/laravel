<?php

namespace Src\Main\Cache;

use ArrayAccess;
use Closure;
use Src\Main\Cache\Services\ICacheStore;
use Src\Main\Support\Traits\InteractsWithTime;

class CacheRepository implements ICacheRepository, ArrayAccess
{
    use InteractsWithTime;

    protected int $default = 3600;
    public function __construct(
        protected ICacheStore $store,
        protected array $config
    ) {}
    public function put(string $key, mixed $value, int $ttl = -1): mixed
    {
        if ($ttl === -1) {
            $this->forever($key, $value);
            return $value;
        }

        $seconds = max($ttl, 0);

        if ($seconds == 0) {
            $this->forget($key);
        }

        return $this->store->put($key, $value, $seconds);
    }
    public function forever(string $key, mixed $value): void
    {
        $this->store->forever($key, $value);
    }
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store->get($key);
    }
    public function has(string $key): bool
    {
        return $this->get($key) != null;
    }
    public function missing(string $key): bool
    {
        return ! $this->has($key);
    }
    public function forget(string $key): void
    {
        $this->store->forget($key);
    }
    public function clear(): void
    {
        $this->store->flush();
    }
    public function pull(string $key): mixed
    {
        $value = $this->get($key);

        $this->forget($key);

        return $value;
    }
    public function increment($key, int $number = 1): void
    {
        $this->store->increment($key, $number);
    }
    public function decrement($key, int $number = 1): void
    {
        $this->store->decrement($key, $number);
    }
    public function remember(string $key, int $ttl, Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value) {
            return $value;
        }

        return $this->put($key, $callback(), $ttl);
    }
    public function rememberForever(string $key, Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }
    public function setDefaultCacheTime(int $seconds): static
    {
        $this->default = $seconds;

        return $this;
    }
    public function getDefaultCacheTime(): int
    {
        return $this->default;
    }
    public function getStore(): ICacheStore
    {
        return $this->store;
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
        $this->put($offset, $value, 3600);
    }
    public function offsetUnset(mixed $offset): void
    {
        $this->delete($offset);
    }
    protected function getSeconds(int $ttl): int
    {
        return max($ttl, 0);
    }
    public function __call(string $method, array $parameters): mixed
    {
        return $this->store->$method(...$parameters);
    }
    public function __clone(): void
    {
        $this->store = clone $this->store;
    }
}
