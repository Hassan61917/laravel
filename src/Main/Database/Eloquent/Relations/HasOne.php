<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;

class HasOne extends HasOneOrMany
{
    public function getResults(): RelationResult
    {
        return (new RelationResult())->setItem($this->query->first());
    }
    public function match(array $models, Collection $results, string $relation): array
    {
        return $this->matchOne($models, $results, $relation);
    }
    public function initRelation(array $models, string $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }
}
