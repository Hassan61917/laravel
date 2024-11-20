<?php

namespace Src\Main\Database\Query\Traits;

use InvalidArgumentException;

trait WithOrders
{
    public array $groups;
    public array $orders;

    public function groupBy(array $groups): static
    {
        $this->groups = array_merge($this->groups ?? [], $groups);

        return $this;
    }
    public function orderBy(string $column, string $direction): static
    {
        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'])) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $this->orders[] = ['column' => $column, 'direction' => $direction];

        return $this;
    }
    public function orderByDesc(string $column): static
    {
        return $this->orderBy($column, 'desc');
    }
    public function latest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'desc');
    }
    public function oldest(string $column = 'created_at'): static
    {
        return $this->orderBy($column, 'asc');
    }
    public function orderByRaw(string $sql, array $bindings = []): static
    {
        $type = 'Raw';

        $this->orders[] = compact('type', 'sql');

        $this->addBinding("order", $bindings);

        return $this;
    }
    public function inRandomOrder(int $seed = null): static
    {
        return $this->orderByRaw($this->grammar->compileRandom($seed));
    }
}
