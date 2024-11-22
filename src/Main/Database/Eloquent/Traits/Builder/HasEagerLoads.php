<?php

namespace Src\Main\Database\Eloquent\Traits\Builder;

use BadMethodCallException;
use Closure;
use Src\Main\Database\Eloquent\Relations\Relation;
use Src\Main\Database\Exceptions\Eloquent\RelationNotFoundException;
use Src\Main\Database\IRelationParser;

trait HasEagerLoads
{
    protected static IRelationParser $relationParser;
    protected array $eagerLoad = [];
    public static function setRelationParser(IRelationParser $relationParser): void
    {
        self::$relationParser = $relationParser;
    }
    public function setEagerLoads(array $eagerLoad): static
    {
        $this->eagerLoad = $eagerLoad;

        return $this;
    }
    public function getEagerLoads(): array
    {
        return $this->eagerLoad;
    }
    public function with(string ...$relations): static
    {
        $eagerLoad = static::$relationParser->parse($relations);

        $this->eagerLoad = array_merge($this->eagerLoad, $eagerLoad);

        return $this;
    }
    public function withOnly(string ...$relations): static
    {
        $this->eagerLoad = [];

        return $this->with(...$relations);
    }
    public function withoutEagerLoads(): static
    {
        return $this->setEagerLoads([]);
    }
    public function eagerLoadRelations(array $models): array
    {
        foreach ($this->eagerLoad as $name => $constraints) {
            if (! str_contains($name, '.')) {
                $models = $this->eagerLoadRelation($models, $name, $constraints);
            }
        }
        return $models;
    }
    protected function eagerLoadRelation(array $models, string $name, Closure $constraints): array
    {
        $relation = $this->getRelation($name);

        $relation->addEagerConstraints($models);

        $constraints($relation);

        return $relation->match(
            $relation->initRelation($models, $name),
            $relation->getEager(),
            $name
        );
    }
    public function getRelation(string $name): Relation
    {
        $relation = Relation::noConstraints(function () use ($name) {
            try {
                return $this->getModel()->newInstance()->$name();
            } catch (BadMethodCallException) {
                throw RelationNotFoundException::make($this->getModel(), $name);
            }
        });

        $nested = $this->relationsNestedUnder($name);

        if (count($nested) > 0) {
            $relation->getQuery()->with($nested);
        }

        return $relation;
    }
    protected function relationsNestedUnder(string $relation): array
    {
        $nested = [];

        foreach ($this->eagerLoad as $name => $constraints) {
            if ($this->isNestedUnder($relation, $name)) {
                $nested[substr($name, strlen($relation . '.'))] = $constraints;
            }
        }

        return $nested;
    }
    protected function isNestedUnder(string $relation, string $name): bool
    {
        return str_contains($name, '.') && str_starts_with($name, $relation . '.');
    }
}
