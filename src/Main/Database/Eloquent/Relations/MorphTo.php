<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Traits\InteractsWithDictionary;

class MorphTo extends BelongsTo
{
    use InteractsWithDictionary;
    protected array $dictionary = [];
    protected array $morphableEagerLoads = [];
    protected array $morphableEagerLoadCounts = [];
    protected Collection $models;
    public function __construct(
        EloquentBuilder $query,
        Model $parent,
        string $foreignKey,
        string $ownerKey,
        protected string $morphType,
        string $relation
    ) {
        parent::__construct($query, $parent, $foreignKey, $ownerKey, $relation);
    }
    public function match(array $models, Collection $results, $relation): array
    {
        return $models;
    }
    public function getMorphType(): string
    {
        return $this->morphType;
    }
    public function getDictionary(): array
    {
        return $this->dictionary;
    }
    public function addEagerConstraints(array $models): void
    {
        $this->buildDictionary($this->models = Collection::make($models));
    }
    public function associate(?Model $model): Model
    {
        if ($model) {
            $foreignKey = $this->ownerKey && $model->{$this->ownerKey}
                ? $this->ownerKey
                : $model->getKeyName();
        }

        $this->parent->setAttribute(
            $this->foreignKey,
            $model?->{$foreignKey}
        );

        $this->parent->setAttribute(
            $this->morphType,
            $model?->getMorphClass()
        );

        return $this->parent->setRelation($this->relationName, (new RelationResult())->setItem($model));
    }
    public function dissociate(): Model
    {
        $this->parent->setAttribute($this->foreignKey, null);

        $this->parent->setAttribute($this->morphType, null);

        return $this->parent->setRelation($this->relationName, null);
    }
    public function touch(): void
    {
        if ($this->child->{$this->foreignKey}) {
            parent::touch();
        }
    }
    public function morphWith(array $with): static
    {
        $this->morphableEagerLoads = array_merge(
            $this->morphableEagerLoads,
            $with
        );

        return $this;
    }
    public function morphWithCount(array $withCount): static
    {
        $this->morphableEagerLoadCounts = array_merge(
            $this->morphableEagerLoadCounts,
            $withCount
        );

        return $this;
    }
    public function getEager(): Collection
    {
        foreach (array_keys($this->dictionary) as $type) {
            $this->matchToMorphParents($type, $this->getResultsByType($type));
        }

        return $this->models;
    }
    protected function buildDictionary(Collection $results): void
    {
        foreach ($results as $model) {
            if ($model->{$this->morphType}) {
                $morphTypeKey = $this->getDictionaryKey($model->{$this->morphType});
                $foreignKeyKey = $this->getDictionaryKey($model->{$this->foreignKey});

                $this->dictionary[$morphTypeKey][$foreignKeyKey][] = $model;
            }
        }
    }
    protected function matchToMorphParents(string $type, Collection $results): void
    {
        foreach ($results as $result) {

            $ownerKey = $this->ownerKey
                ? $this->getDictionaryKey($result->{$this->ownerKey})
                : $result->getKey();

            if (isset($this->dictionary[$type][$ownerKey])) {
                foreach ($this->dictionary[$type][$ownerKey] as $model) {
                    $model->setRelation($this->relationName, (new RelationResult())->setItem($model));
                }
            }
        }
    }
}
