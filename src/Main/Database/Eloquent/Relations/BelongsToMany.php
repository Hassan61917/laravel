<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Traits\InteractsWithDictionary;
use Src\Main\Database\Eloquent\Relations\Traits\InteractsWithPivotTable;
use Src\Main\Database\Exceptions\Eloquent\ModelNotFoundException;
use Src\Main\Pagination\LengthAwarePaginator;
use Src\Main\Utils\Str;

class BelongsToMany extends Relation
{
    use InteractsWithPivotTable,
        InteractsWithDictionary;
    public bool $withTimestamps = false;
    protected string $accessor = 'pivot';
    protected ?string $pivotCreatedAt;
    protected ?string $pivotUpdatedAt;
    protected array $pivotColumns = [];
    protected array $pivotWheres = [];
    protected array $pivotWhereIns = [];
    protected array $pivotWhereNulls = [];
    protected array $pivotValues = [];
    public function __construct(
        EloquentBuilder $query,
        Model $parent,
        protected string $table,
        protected string $foreignPivotKey,
        protected string $relatedPivotKey,
        protected string $parentKey,
        protected string $relatedKey,
        protected ?string $relationName = null
    ) {
        parent::__construct($query, $parent);
    }
    public function getTable(): string
    {
        return $this->table;
    }
    public function getForeignPivotKeyName(): string
    {
        return $this->foreignPivotKey;
    }
    public function getRelatedPivotKeyName(): string
    {
        return $this->relatedPivotKey;
    }
    public function getRelatedKeyName(): string
    {
        return $this->relatedKey;
    }
    public function getQualifiedRelatedKeyName(): string
    {
        return $this->related->qualifyColumn($this->relatedKey);
    }
    public function getQualifiedForeignPivotKeyName(): string
    {
        return $this->qualifyPivotColumn($this->foreignPivotKey);
    }
    public function getQualifiedRelatedPivotKeyName(): string
    {
        return $this->qualifyPivotColumn($this->relatedPivotKey);
    }
    public function getParentKeyName(): string
    {
        return $this->parentKey;
    }
    public function getRelationName(): ?string
    {
        return $this->relationName;
    }
    public function getPivotAccessor(): string
    {
        return $this->accessor;
    }
    public function getPivotColumns(): array
    {
        return $this->pivotColumns;
    }
    public function qualifyPivotColumn(string $column): string
    {
        return str_contains($column, '.')
            ? $column
            : $this->table . '.' . $column;
    }
    public function as(string $accessor): static
    {
        $this->accessor = $accessor;

        return $this;
    }
    public function getExistenceCompareKey(): string
    {
        return $this->getQualifiedForeignPivotKeyName();
    }
    public function wherePivot(string $column, ?string $operator = null, mixed $value = null, string $boolean = 'and')
    {
        $this->pivotWheres[] = compact("column", "operator", "value", "boolean");

        return $this->where($this->qualifyPivotColumn($column), $operator, $value, $boolean);
    }
    public function withPivotValue(string $column, mixed $value = null)
    {
        if (is_null($value)) {
            throw new InvalidArgumentException('The provided value may not be null.');
        }

        $this->pivotValues[] = compact('column', 'value');

        return $this->wherePivot($column, '=', $value);
    }
    public function find(string $id, array $columns = ['*'])
    {
        return $this->where(
            $this->getRelated()->getQualifiedKeyName(),
            '=',
            $id
        )->first($columns);
    }
    public function findMany(array $ids, $columns = ['*']): Collection
    {
        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        return $this->whereKey($ids)->get($columns);
    }
    public function findOrFail(string $id, array $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if ($result) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related), $id);
    }
    public function first(array $columns = ['*']): ?Model
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }
    public function get(array $columns = ['*']): Collection
    {
        $builder = $this->query->applyScopes();

        $columns = isset($builder->getQuery()->columns) ? [] : $columns;

        $models = $builder->addSelect($this->shouldSelect($columns))->getModels();

        $this->hydratePivotRelation($models);

        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }
    public function getResults(): RelationResult
    {
        $items = $this->parent->{$this->parentKey} != null
            ? $this->get()
            : $this->related->newCollection();

        return (new RelationResult())->setItems($items);
    }
    public function paginate(int $perPage = 10, array $columns = ['*']): LengthAwarePaginator
    {
        $this->query->addSelect($this->shouldSelect($columns));

        $paginator = $this->query->paginate($perPage, $columns);

        $this->hydratePivotRelation($paginator->items());

        return $paginator;
    }
    public function touchIfTouching(): void
    {
        if ($this->touchingParent()) {
            $this->getParent()->touch();
        }

        if ($this->getParent()->touches($this->relationName)) {
            $this->touch();
        }
    }
    public function chunk(int $count, callable $callback): bool
    {
        $callback =  function ($results, $page) use ($callback) {
            $this->hydratePivotRelation($results->all());

            return $callback($results, $page);
        };

        return $this->prepareQueryBuilder()->chunk($count, $callback);
    }
    public function each(callable $callback, int $count = 100): bool
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if (!$callback($value, $key)) {
                    return false;
                }
            }
            return true;
        });
    }
    public function allRelatedIds(): Collection
    {
        return $this->newPivotQuery()->pluck($this->relatedPivotKey);
    }
    public function touch(): void
    {
        $columns = [
            $this->related->getUpdatedAtColumn() => $this->related->freshTimestampString(),
        ];

        $ids = $this->allRelatedIds();

        if ($ids->isNotEmpty()) {
            $this->getRelated()->newQueryWithoutRelationships()->whereKey($ids)->update($columns);
        }
    }
    public function save(Model $model, array $attributes = [], bool $touch = true): Model
    {
        $model->save();

        $this->attach($model, $attributes, $touch);

        return $model;
    }
    public function saveMany(array $models, array $attributes = []): array
    {
        foreach ($models as $model) {
            $this->save($model, $attributes, false);
        }

        $this->touchIfTouching();

        return $models;
    }
    public function create(array $attributes = [], array $joining = [], bool $touch = true): Model
    {
        $instance = $this->related->newInstance($attributes);

        $instance->save();

        $this->attach($instance, $joining, $touch);

        return $instance;
    }
    public function createMany(array $records, array $joining = []): array
    {
        $instances = [];

        foreach ($records as $record) {
            $instances[] = $this->create($record, $joining, false);
        }

        $this->touchIfTouching();

        return $instances;
    }
    public function limit(int $value): static
    {
        $this->query->limit($value);

        return $this;
    }
    public function take(int $value): static
    {
        return $this->limit($value);
    }
    public function withTimestamps(?string $createdAt = null, ?string $updatedAt = null): static
    {
        $this->withTimestamps = true;

        $this->pivotCreatedAt = $createdAt;
        $this->pivotUpdatedAt = $updatedAt;

        return $this->withPivot([$this->createdAt(), $this->updatedAt()]);
    }
    public function createdAt(): string
    {
        return $this->pivotCreatedAt ?? $this->parent->getCreatedAtColumn();
    }
    public function updatedAt(): string
    {
        return $this->pivotUpdatedAt ?? $this->parent->getUpdatedAtColumn();
    }
    public function getQualifiedParentKeyName(): string
    {
        return $this->parent->qualifyColumn($this->parentKey);
    }
    public function addEagerConstraints(array $models): void
    {
        $this->whereInEager(
            $this->getQualifiedForeignPivotKeyName(),
            $this->getKeys($models, $this->parentKey)
        );
    }
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $items = $this->related->newCollection();
            $model->setRelation($relation, (new RelationResult())->setItems($items));
        }
        return $models;
    }
    public function match(array $models, Collection $results, string $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $this->getDictionaryKey($model->{$this->parentKey});

            if (isset($dictionary[$key])) {
                $model->setRelation(
                    $relation,
                    $this->related->newCollection($dictionary[$key])
                );
            }
        }

        return $models;
    }
    protected function addConstraints(): void
    {
        $this->performJoin();

        if (static::$constraints) {
            $this->addWhereConstraints();
        }
    }
    protected function performJoin(?EloquentBuilder $query = null): static
    {
        $query = $query ?: $this->query;

        $query->join(
            $this->table,
            $this->getQualifiedRelatedKeyName(),
            '=',
            $this->getQualifiedRelatedPivotKeyName()
        );

        return $this;
    }
    protected function addWhereConstraints(): static
    {
        $this->query->where(
            $this->getQualifiedForeignPivotKeyName(),
            '=',
            $this->parent->{$this->parentKey}
        );

        return $this;
    }
    protected function shouldSelect(array $columns = ['*']): array
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        return array_merge($columns, $this->aliasedPivotColumns());
    }
    protected function aliasedPivotColumns(): array
    {
        $defaults = [$this->foreignPivotKey, $this->relatedPivotKey];

        return collect(array_merge($defaults, $this->pivotColumns))->map(
            fn($column) => $this->qualifyPivotColumn($column) . " as pivot_$column"
        )->unique()->all();
    }
    protected function hydratePivotRelation(array $models): void
    {
        foreach ($models as $model) {
            $pivot = $this->newExistingPivot($this->migratePivotAttributes($model));
            $model->setRelation($this->accessor, (new RelationResult())->setPivot($pivot));
        }
    }
    protected function migratePivotAttributes(Model $model): array
    {
        $values = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (str_starts_with($key, 'pivot_')) {
                $values[substr($key, 6)] = $value;
                unset($model->$key);
            }
        }

        return $values;
    }
    protected function touchingParent(): bool
    {
        return $this->getRelated()->touches($this->guessInverseRelation());
    }
    protected function guessInverseRelation(): string
    {
        return Str::camel(Str::pluralStudly(class_basename($this->getParent())));
    }
    protected function prepareQueryBuilder(): EloquentBuilder
    {
        return $this->query->addSelect($this->shouldSelect());
    }
    protected function buildDictionary(Collection $results): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $value = $this->getDictionaryKey($result->{$this->accessor}->{$this->foreignPivotKey});

            $dictionary[$value][] = $result;
        }

        return $dictionary;
    }
}
