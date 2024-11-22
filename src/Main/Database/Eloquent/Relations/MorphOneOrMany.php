<?php

namespace Src\Main\Database\Eloquent\Relations;

use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;

abstract class MorphOneOrMany extends HasOneOrMany
{
    protected string $morphClass;
    public function __construct(
        EloquentBuilder $query,
        Model $parent,
        protected string $morphType,
        string $id,
        string $localKey
    ) {
        $this->morphClass = $parent->getMorphClass();

        parent::__construct($query, $parent, $id, $localKey);
    }
    public function getQualifiedMorphType(): string
    {
        return $this->morphType;
    }
    public function getMorphType(): string
    {
        return last(explode('.', $this->getQualifiedMorphType()));
    }
    public function getMorphClass(): string
    {
        return $this->morphClass;
    }
    public function getRelationExistenceQuery(EloquentBuilder $query, EloquentBuilder $parentQuery, array $columns = ['*']): EloquentBuilder
    {
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns)->where(
            $query->qualifyColumn($this->getMorphType()),
            $this->morphClass
        );
    }
    public function addEagerConstraints(array $models): void
    {
        parent::addEagerConstraints($models);

        $this->getRelationQuery()->where($this->morphType, $this->morphClass);
    }
    protected function addConstraints(): void
    {
        if (static::$constraints) {
            $this->getRelationQuery()->where($this->morphType, $this->morphClass);

            parent::addConstraints();
        }
    }
    protected function setForeignAttributesForCreate(Model $model): void
    {
        $model->{$this->getForeignKeyName()} = $this->getParentKey();

        $model->{$this->getMorphType()} = $this->getMorphClass();
    }
}
