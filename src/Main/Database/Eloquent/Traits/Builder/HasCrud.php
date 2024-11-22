<?php

namespace Src\Main\Database\Eloquent\Traits\Builder;

use Generator;
use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Pagination\LengthAwarePaginator;
use Src\Main\Pagination\Paginator;

trait HasCrud
{
    public function touch(string $column = null): int
    {
        $time = $this->model->freshTimestamp();

        if ($column) {
            return $this->toBase()->update([$column => $time]);
        }

        $column = $this->model->getUpdatedAtColumn();

        if (! $this->model->usesTimestamps()) {
            return 0;
        }

        return $this->toBase()->update([$column => $time]);
    }
    public function make(array $attributes = []): Model
    {
        return $this->newModelInstance($attributes);
    }
    public function create(array $attributes = []): Model
    {
        $instance = $this->newModelInstance($attributes);

        $instance->save();

        return $instance;
    }
    public function createOrFirst(array $attributes = [], array $values = []): Model
    {
        $instance = $this->where(...$attributes)->first();

        if ($instance) {
            return $instance;
        }

        return $this->create(array_merge($attributes, $values));
    }
    public function insertGetId(array $attributes, string $keyName): int
    {
        return $this->query->insertGetId($attributes, $keyName);
    }
    public function get(array $columns = ['*']): Collection
    {
        $builder = $this->applyScopes();

        $models = $builder->getModels($columns);

        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $builder->getModel()->newCollection($models);
    }
    public function cursor(): Generator
    {
        $generator = $this->applyScopes()->query->cursor();
        while ($generator->valid()) {
            $record = $generator->current();
            yield $this->newModelInstance()->newFromBuilder((array)$record);
        }
    }
    public function getModels(array $columns = ['*']): array
    {
        return $this->model->hydrate(
            $this->query->get($columns)->all()
        )->all();
    }
    public function update(array $values): int
    {
        return $this->toBase()->update($values);
    }
    public function delete(): int
    {
        return $this->toBase()->delete();
    }
    public function forceDelete(): int
    {
        return $this->query->delete();
    }
    public function increment(string $column, int $amount = 1, array $extra = []): int
    {
        return $this->toBase()->increment($column, $amount, $extra);
    }
    public function decrement(string $column, int $amount = 1, array $extra = []): int
    {
        return $this->toBase()->decrement($column, $amount, $extra);
    }
    public function paginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null, int $total = null): LengthAwarePaginator
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $total ?? $this->toBase()->getCountForPagination();

        $perPage = $perPage ?: $this->model->getPerPage();

        $results = $total
            ? $this->forPage($page, $perPage)->get($columns)
            : $this->model->newCollection();

        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function simplePaginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null): Paginator
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $perPage = $perPage ?: $this->model->getPerPage();

        $this->skip(($page - 1) * $perPage)->take($perPage + 1);

        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function latest(string $column = null): static
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->latest($column);

        return $this;
    }
    public function oldest(string $column = null): static
    {
        if (is_null($column)) {
            $column = $this->model->getCreatedAtColumn() ?? 'created_at';
        }

        $this->query->oldest($column);

        return $this;
    }
}
