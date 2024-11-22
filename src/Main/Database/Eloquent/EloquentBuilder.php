<?php

namespace Src\Main\Database\Eloquent;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\Traits\Builder\HasCrud;
use Src\Main\Database\Eloquent\Traits\Builder\HasEagerLoads;
use Src\Main\Database\Eloquent\Traits\Builder\HasScopes;
use Src\Main\Database\Eloquent\Traits\Builder\HasWheres;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Database\Traits\BuildsQueries;
use Src\Main\Support\Traits\ForwardsCalls;

class EloquentBuilder
{
    use HasScopes,
        HasWheres,
        HasCrud,
        BuildsQueries,
        HasEagerLoads,
        ForwardsCalls;

    protected Model $model;

    public function __construct(
        protected QueryBuilder $query
    ) {}
    public function setModel(Model $model): static
    {
        $this->model = $model;

        $this->query->from($model->getTable());

        return $this;
    }
    public function getModel(): Model
    {
        return $this->model;
    }
    public function getQuery(): QueryBuilder
    {
        return $this->query;
    }
    public function toBase(): QueryBuilder
    {
        return $this->applyScopes()->getQuery();
    }
    public function qualifyColumn(string $column): string
    {
        return $this->model->qualifyColumn($column);
    }
    public function qualifyColumns(array $columns): array
    {
        return $this->model->qualifyColumns(...$columns);
    }
    public function hydrate(array $items): Collection
    {
        $instance = $this->newModelInstance();

        $models = array_map(fn($item) => $instance->newFromBuilder((array) $item), $items);

        return $instance->newCollection($models);
    }
    public function newModelInstance(array $attributes = []): Model
    {
        return $this->model->newInstance($attributes);
    }
    public function clone(): static
    {
        return clone $this;
    }
    public function __call(string $name, array $arguments): static
    {
        $this->forwardCallTo($this->query, $name, $arguments);

        return $this;
    }
}
