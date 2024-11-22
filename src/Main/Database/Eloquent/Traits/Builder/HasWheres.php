<?php

namespace Src\Main\Database\Eloquent\Traits\Builder;

use Closure;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Exceptions\Eloquent\ModelNotFoundException;

trait HasWheres
{
    public function where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        $this->query->where($column, $operator, $value, $boolean);

        return $this;
    }
    public function orWhere(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->where($column, $operator, $value, 'or');
    }
    public function whereNot(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->where($column, $operator, $value, 'not');
    }
    public function orWhereNot(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }
    public function whereKey(string $id): static
    {
        return $this->where($this->model->getQualifiedKeyName(), '=', $id);
    }
    public function whereKeys(string ...$ids): static
    {
        $this->query->whereIn($this->model->getQualifiedKeyName(), $ids);

        return $this;
    }
    public function whereKeysNot(string ...$ids): static
    {
        $this->query->whereNotIn($this->model->getQualifiedKeyName(), $ids);

        return $this;
    }
    public function whereKeyNot(int|string $id): static
    {
        if ($this->model->getKeyType() != 'string') {
            $id = (string) $id;
        }

        return $this->where($this->model->getQualifiedKeyName(), '!=', $id);
    }
    public function whereNested(Closure $closure, string $boolean = 'and'): static
    {
        $query = $this->model->newQueryWithoutRelationships();

        $closure($query);

        $this->query->addNestedWhereQuery($query->getQuery(), $boolean);

        return $this;
    }
    public function firstWhere(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->where(...func_get_args())->first();
    }
    public function find(string $id, array $columns = ['*']): ?Model
    {
        return $this->whereKey($id)->first($columns);
    }
    public function findMany(array $ids, array $columns = ['*']): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->whereKeys(...$ids)->get($columns)->toArray();
    }
    public function findOrFail(string $id, array $columns = ['*']): ?Model
    {
        $result = $this->find($id, $columns);

        if (is_null($result)) {
            throw (new ModelNotFoundException())->setModel(get_class($this->model), $id);
        }

        return $result;
    }
    public function findOrNew(string $id, array $columns = ['*']): Model
    {
        $model = $this->find($id, $columns);

        if ($model) {
            return $model;
        }

        return $this->newModelInstance();
    }
    public function firstOrFail(array $columns = ['*'])
    {
        $model = $this->first($columns);

        if ($model) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->model));
    }
}
