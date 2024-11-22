<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;

class HasMany extends HasOneOrMany
{
    public function getResults(): RelationResult
    {
        $items = $this->getParentKey()
            ? $this->query->get()
            : $this->related->newCollection();

        return (new RelationResult())->setItems($items);
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
