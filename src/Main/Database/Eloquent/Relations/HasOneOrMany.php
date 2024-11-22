<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Traits\InteractsWithDictionary;

abstract class HasOneOrMany extends Relation
{
    use InteractsWithDictionary;
    public function __construct(
        EloquentBuilder $query,
        Model $parent,
        protected string $foreignKey,
        protected string $localKey
    ) {
        parent::__construct($query, $parent);
    }
    public function getParentKey(): mixed
    {
        return $this->parent->getAttribute($this->localKey);
    }
    public function getQualifiedForeignKeyName(): string
    {
        return $this->foreignKey;
    }
    public function getExistenceCompareKey(): string
    {
        return $this->getQualifiedForeignKeyName();
    }
    public function getQualifiedParentKeyName(): string
    {
        return $this->parent->qualifyColumn($this->localKey);
    }
    public function make(array $attributes = []): Model
    {
        $instance = $this->related->newInstance($attributes);

        $this->setForeignAttributesForCreate($instance);

        return $instance;
    }
    public function makeMany(array $records): Collection
    {
        $instances = $this->related->newCollection();

        foreach ($records as $record) {
            $instances->push($this->make($record));
        }

        return $instances;
    }
    public function save(Model $model): ?Model
    {
        $this->setForeignAttributesForCreate($model);

        return $model->save() ? $model : null;
    }
    public function saveMany(iterable $models): iterable
    {
        foreach ($models as $model) {
            $this->save($model);
        }

        return $models;
    }
    public function create(array $attributes = []): Model
    {
        $instance = $this->make($attributes);

        $instance->save();

        return $instance;
    }
    public function createMany(iterable $records): Collection
    {
        $instances = $this->related->newCollection();

        foreach ($records as $record) {
            $instances->push($this->create($record));
        }

        return $instances;
    }
    public function take($value): static
    {
        return $this->limit($value);
    }
    public function limit(int $value): static
    {
        if ($this->parent->exists) {
            $this->query->limit($value);
        }

        return $this;
    }
    public function matchOne(array $models, Collection $results, string $relation): array
    {
        return $this->matchOneOrMany($models, $results, $relation, 'one');
    }
    public function matchMany(array $models, Collection $results, string $relation): array
    {
        return $this->matchOneOrMany($models, $results, $relation, 'many');
    }
    public function addEagerConstraints(array $models): void
    {
        $this->whereInEager(
            $this->foreignKey,
            $this->getKeys($models, $this->localKey),
            $this->getRelationQuery()
        );
    }

    protected function addConstraints(): void
    {
        if (static::$constraints) {

            $this->query->where($this->foreignKey, '=', $this->getParentKey());

            $this->query->whereNotNull([$this->foreignKey]);
        }
    }
    protected function setForeignAttributesForCreate(Model $model): void
    {
        $model->setAttribute($this->getForeignKeyName(), $this->getParentKey());
    }
    protected function getForeignKeyName(): string
    {
        $segments = explode('.', $this->foreignKey);

        return end($segments);
    }
    protected function matchOneOrMany(array $models, Collection $results, string $relation, string $type): array
    {
        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            $key = $this->getDictionaryKey($model->getAttribute($this->localKey));

            if (isset($dictionary[$key])) {
                $model->setRelation(
                    $relation,
                    $this->getRelationValue($dictionary, $key, $type)
                );
            }
        }

        return $models;
    }
    protected function buildDictionary(Collection $results): array
    {
        $key =  $this->getForeignKeyName();

        return $results->mapToDictionary(
            fn($result) => [$this->getDictionaryKey($result->{$key}) => $result]
        )->all();
    }
    protected function getRelationValue(array $dictionary, string $key, string $type): mixed
    {
        $value = $dictionary[$key];

        return $type === 'one' ? reset($value) : $this->related->newCollection($value);
    }
}
