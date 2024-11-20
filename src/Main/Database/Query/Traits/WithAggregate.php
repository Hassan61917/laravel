<?php

namespace Src\Main\Database\Query\Traits;

trait WithAggregate
{
    public array $aggregate;
    public function count(array $columns = ['*']): int
    {
        return $this->aggregate("count", $columns);
    }
    public function min($column): int
    {
        return $this->aggregate("min", [$column]);
    }
    public function max(string $column): int
    {
        return $this->aggregate("sum", [$column]);
    }
    public function sum(string $column): int
    {
        $result = $this->aggregate("sum", [$column]);

        return $result ?: 0;
    }
    public function avg(string $column): int
    {
        return $this->aggregate("avg", [$column]);
    }
    public function average(string $column): int
    {
        return $this->avg($column);
    }
    public function aggregate(string $function, array $columns = ['*']): int
    {
        $results = $this->cloneWithout(['columns'])
            ->cloneWithoutBindings(['select'])
            ->setAggregate($function, $columns)
            ->get($columns);

        return $results[0]->{"aggregate"} ?? 0;
    }
    public function cloneWithout(array $properties): static
    {
        $clone = $this->clone();

        foreach ($properties as $property) {
            unset($clone->$property);
        }
        return $clone;
    }
    public function cloneWithoutBindings(array $properties): static
    {
        $clone = $this->clone();

        foreach ($properties as $type) {
            $clone->bindings[$type] = [];
        }
        return $clone;
    }
    protected function setAggregate(string $function, array $columns): static
    {
        $this->aggregate = compact('function', 'columns');

        if (isset($this->groups)) {
            $this->orders = [];

            $this->bindings['order'] = [];
        }

        return $this;
    }
}
