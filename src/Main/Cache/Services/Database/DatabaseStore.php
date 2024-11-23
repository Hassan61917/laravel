<?php

namespace Src\Main\Cache\Services\Database;

use Carbon\Carbon;
use Src\Main\Cache\Services\ICacheStore;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Support\Traits\InteractsWithTime;

class DatabaseStore implements ICacheStore
{
    use InteractsWithTime;
    protected string $table;
    protected ?string $prefix = null;
    public function __construct(
        protected Connection $connection,
        protected array $config
    ) {
        $this->table = $this->config['table'];
        $this->prefix = $this->config['prefix'] ?? null;
    }
    public function put(string $key, mixed $value, int $seconds = null): mixed
    {
        $key = $this->prefix . $key;

        $value = serialize($value);

        $expiration = $this->availableAt($seconds);

        $values = compact('key', 'value', 'expiration');

        if ($this->exists($key)) {
            $this->update($key, $values);
        } else {
            $this->insert($values);
        }

        return $value;
    }
    public function forget(string $key): void
    {
        $this->getItem($key)->delete();
    }
    public function forever(string $key, mixed $value): void
    {
        $this->put($key, $value, Carbon::now()->addDays(30)->getTimestamp());
    }
    public function flush(): void
    {
        $this->table()->delete();
    }
    public function get(string $key): mixed
    {
        $item = $this->getItem($key)->first();

        if (!$item) {
            return null;
        }

        if ($this->currentTime() >= $item->expiration) {
            $this->forgetIfExpired($key);

            return null;
        }

        return unserialize($item->value);
    }
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    protected function table(): QueryBuilder
    {
        return $this->connection->table($this->table);
    }
    protected function forgetIfExpired(string $key): void
    {
        $this->getItem($key)
            ->where('expiration', '<=', $this->currentTime())
            ->delete();
    }
    protected function getItem(string $key): QueryBuilder
    {
        return $this->table()->where('key', '=', $this->prefix . $key);
    }
    public function increment(int $key, int $number = 1): void
    {
        $this->incrementOrDecrement($key, $number);
    }
    public function decrement(string $key, int $number = 1): void
    {
        $this->incrementOrDecrement($key, $number * -1);
    }
    protected function exists(string $key): bool
    {
        return $this->getItem($key)->exists();
    }
    protected function incrementOrDecrement(string $key, int $number): void
    {
        $item = $this->getItem($key)->first();

        if (!$item) {
            return;
        }

        $value = unserialize($item->value);

        if (! is_numeric($value)) {
            return;
        }

        $this->update($key, ["value" => $value + $number]);
    }
    protected function insert(array $values): void
    {
        $this->table()->insert($values);
    }
    protected function update(string $key, array $values): void
    {
        $this->getItem($key)->update($values);
    }
}
