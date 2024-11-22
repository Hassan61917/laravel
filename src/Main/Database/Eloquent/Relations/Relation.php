<?php

namespace Src\Main\Database\Eloquent\Relations;

use Closure;
use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Support\Traits\ForwardsCalls;

abstract class Relation
{
    use ForwardsCalls;
    protected static bool $constraints = true;
    protected static bool $requireMorphMap = false;
    protected static array $morphMap = [];
    protected static int $selfJoinCount = 0;
    protected bool $eagerKeysWereEmpty = false;
    protected Model $related;
    public function __construct(
        protected EloquentBuilder $query,
        protected Model $parent
    ) {
        $this->related = $this->query->getModel();
        $this->addConstraints();
    }
    public static function noConstraints(Closure $callback): mixed
    {
        $prev = static::$constraints;

        static::$constraints = false;

        try {
            return $callback();
        } finally {
            static::$constraints = $prev;
        }
    }
    public static function requireMorphMap(bool $requireMorphMap = true): void
    {
        static::$requireMorphMap = $requireMorphMap;
    }
    public static function requiresMorphMap(): bool
    {
        return static::$requireMorphMap;
    }
    public static function enforceMorphMap(array $map, bool $merge = true): array
    {
        static::requireMorphMap();

        return static::morphMap($map, $merge);
    }
    public static function morphMap(array $map = [], bool $merge = true): array
    {
        $map = static::buildMorphMapFromModels($map);

        static::$morphMap = $merge ? array_merge($map, static::$morphMap) : $map;

        return static::$morphMap;
    }
    protected static function buildMorphMapFromModels(array $models = []): array
    {
        if (empty($models)) {
            return $models;
        }

        return array_combine(
            array_map(fn($model) => (new $model)->getTable(), $models),
            $models
        );
    }
    public static function getMorphedModel(string $alias): ?string
    {
        return static::$morphMap[$alias] ?? null;
    }
    public function getQuery(): EloquentBuilder
    {
        return $this->query;
    }
    public function getParent(): Model
    {
        return $this->parent;
    }
    public function getRelated(): Model
    {
        return $this->related;
    }
    public function getBaseQuery(): QueryBuilder
    {
        return $this->query->getQuery();
    }
    public function toBase(): QueryBuilder
    {
        return $this->query->toBase();
    }
    public function getQualifiedParentKeyName(): string
    {
        return $this->parent->getQualifiedKeyName();
    }
    public function createdAt(): string
    {
        return $this->parent->getCreatedAtColumn();
    }
    public function updatedAt(): string
    {
        return $this->parent->getUpdatedAtColumn();
    }
    public function relatedCreatedAt(): string
    {
        return $this->related->getCreatedAtColumn();
    }
    public function relatedUpdatedAt(): string
    {
        return $this->related->getUpdatedAtColumn();
    }
    public function getEager(): Collection
    {
        return $this->eagerKeysWereEmpty
            ? $this->query->getModel()->newCollection()
            : $this->get();
    }
    public function get(array $columns = ['*']): Collection
    {
        return $this->query->get($columns);
    }
    public function rawUpdate(array $attributes = []): int
    {
        return $this->query->withoutGlobalScopes()->update($attributes);
    }
    public function touch(): void
    {
        $model = $this->getRelated();

        $this->rawUpdate([
            $model->getUpdatedAtColumn() => $model->freshTimestampString(),
        ]);
    }
    public function getRelationExistenceCountQuery(EloquentBuilder $query, EloquentBuilder $parentQuery): QueryBuilder
    {
        return $this->getRelationExistenceQuery(
            $query,
            $parentQuery,
            ['count(*)']
        )->setBindings([], 'select');
    }
    public function getRelationExistenceQuery(EloquentBuilder $query, EloquentBuilder $parentQuery, array $columns = ['*']): EloquentBuilder
    {
        return $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(),
            '=',
            $this->getExistenceCompareKey()
        );
    }
    protected function whereInEager(string $key, array $modelKeys, ?EloquentBuilder $query = null): void
    {
        $query = $query ?: $this->query;

        $query->whereIn($key, $modelKeys);

        if (empty($modelKeys)) {
            $this->eagerKeysWereEmpty = true;
        }
    }
    public function getRelationCountHash(bool $incrementJoinCount = true): string
    {
        return 'laravel_reserved_' . ($incrementJoinCount ? static::$selfJoinCount++ : static::$selfJoinCount);
    }
    protected function getRelationQuery(): EloquentBuilder
    {
        return $this->query;
    }
    protected function getKeys(array $models, string $key): array
    {
        $result = collect($models);

        $result = $result->map(fn(Model $model) => $model->getAttribute($key));

        return $result->values()->unique(null, true)->sort()->all();
    }
    public function getExistenceCompareKey(): string
    {
        return "";
    }
    public abstract function getResults(): RelationResult;
    public abstract function addEagerConstraints(array $models): void;
    public abstract function match(array $models, Collection $results, string $relation): array;
    public abstract function initRelation(array $models, string $relation): array;
    protected abstract function addConstraints(): void;
    public function __call(string $method, array $parameters)
    {
        return $this->forwardDecoratedCallTo($this->query, $method, $parameters);
    }
    public function __clone()
    {
        $this->query = clone $this->query;
    }
}
