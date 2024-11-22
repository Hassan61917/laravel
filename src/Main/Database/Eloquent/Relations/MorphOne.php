<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;

class MorphOne extends MorphOneOrMany
{
    public function getResults(): RelationResult
    {
        return (new RelationResult())->setItem($this->query->first());
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
        return $this->matchOne($models, $results, $relation);
    }
}
