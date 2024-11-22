<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Traits\InteractsWithDictionary;

class BelongsTo extends Relation
{
    use InteractsWithDictionary;
    protected Model $child;
    public function __construct(
        EloquentBuilder $query,
        Model $parent,
        protected string $foreignKey,
        protected string $ownerKey,
        protected string $relationName
    ) {
        $this->child = $parent;
        parent::__construct($query, $parent);
    }
    public function getChild(): Model
    {
        return $this->child;
    }
    public function getForeignKeyName(): string
    {
        return $this->foreignKey;
    }
    public function getQualifiedForeignKeyName(): string
    {
        return $this->child->qualifyColumn($this->foreignKey);
    }
    public function getParentKey()
    {
        return $this->getForeignKeyFrom($this->child);
    }
    public function getRelationName(): string
    {
        return $this->relationName;
    }
    public function getOwnerKeyName(): string
    {
        return $this->ownerKey;
    }
    public function getQualifiedOwnerKeyName(): string
    {
        return $this->related->qualifyColumn($this->ownerKey);
    }
    protected function getRelatedKeyFrom(Model $model): string
    {
        return $model->{$this->ownerKey};
    }
    public function associate(Model $model): Model
    {
        if (!$model instanceof $this->related) {
            $class  = get_class($this->related);
            throw new \InvalidArgumentException("model must be an instance of $class");
        }

        $ownerKey = $model->getAttribute($this->ownerKey);

        $this->child->setAttribute($this->foreignKey, $ownerKey);

        $this->child->setRelation($this->relationName, (new RelationResult())->setItem($model));

        return $this->child;
    }
    public function dissociate(): Model
    {
        $this->child->setAttribute($this->foreignKey, null);

        return $this->child->setRelation($this->relationName, null);
    }
    public function getResults(): RelationResult
    {
        return (new RelationResult())->setItem($this->query->first());
    }
    public function addEagerConstraints(array $models): void
    {
        $key = $this->related->getTable() . '.' . $this->ownerKey;

        $this->whereInEager($key, $this->getEagerModelKeys($models));
    }
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }
    public function match(array $models, Collection $results, string $relation): array
    {
        $dictionary = [];

        foreach ($results as $result) {
            $attribute = $this->getDictionaryKey($this->getRelatedKeyFrom($result));

            $dictionary[$attribute] = $result;
        }

        foreach ($models as $model) {
            $attribute = $this->getDictionaryKey($this->getForeignKeyFrom($model));

            if (isset($dictionary[$attribute])) {
                $item = $dictionary[$attribute];

                $model->setRelation($relation, (new RelationResult())->setItem($item));
            }
        }

        return $models;
    }
    protected function addConstraints(): void
    {
        if (static::$constraints) {
            $this->query->where($this->getOwnerKey(), '=', $this->getForeignKeyFrom($this->child));
        }
    }
    protected function getOwnerKey(): string
    {
        $table = $this->related->getTable();

        return $table . '.' . $this->ownerKey;
    }
    protected function getForeignKeyFrom(Model $model): mixed
    {
        return $model->{$this->foreignKey};
    }
    protected function getEagerModelKeys(array $models): array
    {
        $keys = [];

        foreach ($models as $model) {
            $value = $this->getForeignKeyFrom($model);

            if ($value) {
                $keys[] = $value;
            }
        }

        sort($keys);

        return array_values(array_unique($keys));
    }
}
