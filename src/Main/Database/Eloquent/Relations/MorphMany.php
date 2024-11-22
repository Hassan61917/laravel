<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;

class MorphMany extends MorphOneOrMany
{
    public function getResults(): RelationResult
    {
        $items =  $this->getParentKey() != null
            ? $this->query->get()
            : $this->related->newCollection();

        return (new RelationResult())->setItems($items);
    }
    public function one()
    {
        return MorphOne::noConstraints(fn() => new MorphOne(
            $this->getQuery(),
            $this->getParent(),
            $this->morphType,
            $this->foreignKey,
            $this->localKey
        ));
    }
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $items = $this->related->newCollection();
            $model->setRelation($relation, (new RelationResult())->setItems($items));
        }

        return $models;
    }

    public function match(array $models, Collection $results, string $relation): array
    {
        return $this->matchMany($models, $results, $relation);
    }
}
