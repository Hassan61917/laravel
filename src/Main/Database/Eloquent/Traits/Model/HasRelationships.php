<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\Relations\Relation;
use Src\Main\Database\Eloquent\Relations\RelationResult;
use Src\Main\Utils\Str;

trait HasRelationships
{
    use HandleHas,
        HandleBelongs,
        HandleMorph;

    protected static array $relationResolvers = [];

    protected array $relations = [];
    protected array $touches = [];
    public static function resolveRelationUsing(string $name, Closure $callback): void
    {
        static::$relationResolvers = array_replace_recursive(
            static::$relationResolvers,
            [static::class => [$name => $callback]]
        );
    }
    public static function getActualClassNameForMorph(string $class)
    {
        return Arr::get(Relation::morphMap() ?: [], $class, $class);
    }
    public function setTouchedRelations(array $touches): static
    {
        $this->touches = $touches;

        return $this;
    }
    public function getTouchedRelations(): array
    {
        return $this->touches;
    }
    public function setRelations(array $relations): static
    {
        $this->relations = $relations;

        return $this;
    }
    public function getRelations(): array
    {
        return $this->relations;
    }
    public function getForeignKey(): string
    {
        return Str::snake(class_basename($this)) . '_' . $this->getKeyName();
    }
    public function setRelation(string $relation, ?RelationResult $value): static
    {
        $this->relations[$relation] = $value;

        return $this;
    }
    public function unsetRelation(string $relation): static
    {
        unset($this->relations[$relation]);

        return $this;
    }
    public function getRelation(string $relation)
    {
        return $this->relations[$relation];
    }
    public function touches(string $relation): bool
    {
        return in_array($relation, $this->getTouchedRelations());
    }
    public function touchOwners(): void
    {
        foreach ($this->getTouchedRelations() as $relation) {
            $this->$relation()->touch();

            if ($this->$relation instanceof self) {
                $this->$relation->touchOwners();
            } elseif ($this->$relation instanceof Collection) {
                $this->$relation->each->touchOwners();
            }
        }
    }
    public function relationLoaded($key): bool
    {
        return array_key_exists($key, $this->relations);
    }
    public function withoutRelations(): static
    {
        $model = clone $this;

        return $model->unsetRelations();
    }
    public function relationResolver(string $class, string $key)
    {
        $resolver = static::$relationResolvers[$class][$key] ?? null;

        if ($resolver) {
            return $resolver;
        }

        $parent = get_parent_class($class);

        if ($parent) {
            return $this->relationResolver($parent, $key);
        }

        return null;
    }
}
