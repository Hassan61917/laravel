<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Illuminate\Support\Collection;
use LogicException;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Relation;
use Src\Main\Database\Eloquent\Relations\RelationResult;
use Src\Main\Database\Exceptions\Eloquent\MissingAttributeException;

trait HasAttributes
{
    use HasMutator,
        HasCasts;

    protected array $attributes = [];
    protected array $original = [];
    protected array $changes = [];
    protected array $appends = [];
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    public function syncOriginal(): static
    {
        $this->original = $this->getAttributes();

        return $this;
    }
    public function syncOriginalAttributes(string ...$keys): static
    {
        $modelAttributes = $this->getAttributes();

        foreach ($keys as $key) {
            $this->original[$key] = $modelAttributes[$key];
        }

        return $this;
    }
    public function getRawOriginal(?string $key = null,mixed $default = null):mixed
    {
        if(is_null($key)) {
            return $this->original;
        }

        return $this->original[$key] ?? $default;
    }
    public function setRawAttributes(array $attributes, bool $sync = false): static
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }

        return $this;
    }
    public function setAttribute(string $key, mixed $value): static
    {
        if ($this->hasSetMutator($key)) {
            return $this->runSetMutator($key, $value);
        }

        $value = $this->toCast($key, $value);

        $this->attributes[$key] = $value;

        return $this;
    }
    public function hasAttribute(string $key): bool
    {
        if (!$key) {
            return false;
        }

        return array_key_exists($key, $this->attributes) ||
            $this->hasCast($key) ||
            $this->hasGetMutator($key);
    }
    public function getAttribute(string $key): mixed
    {
        if ($this->hasAttribute($key)) {
            return $this->getAttributeValue($key);
        }


        if ($this->isRelation($key) || $this->relationLoaded($key)) {
            return $this->getRelationValue($key);
        }

        throw new MissingAttributeException($this, $key);
    }
    public function getAttributeValue(string $key): mixed
    {
        return $this->transformModelValue($key, $this->getAttributeFromArray($key));
    }
    public function isClean(array $attributes = []): bool
    {
        return ! $this->isDirty($attributes);
    }
    public function isDirty(array $attributes = []): bool
    {
        return $this->hasChanges($this->getDirty(), $attributes);
    }
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->getAttributes() as $key => $value) {
            if (! $this->originalIsEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }
    public function syncChanges(): static
    {
        $this->changes = $this->getDirty();

        return $this;
    }
    protected function transformModelValue(string $key, mixed $value): mixed
    {
        if ($this->hasGetMutator($key)) {
            return $this->runGetMutator($key, $value);
        }

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        if ($value && in_array($key, $this->getDates())) {
            return $this->asDateTime($key,$value);
        }

        return $value;
    }
    protected function getAttributeFromArray(string $key): mixed
    {
        return $this->getAttributes()[$key] ?? null;
    }
    protected function hasChanges(array $changes, array $attributes = []): bool
    {
        if (empty($attributes)) {
            return count($changes) > 0;
        }

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }
    protected function originalIsEquivalent(string $key): bool
    {
        if (! array_key_exists($key, $this->original)) {
            return false;
        }

        $attribute = $this->attributes[$key];

        $original = $this->original[$key];

        if (is_null($attribute)) {
            return false;
        }

        if ($attribute === $original) {
            return true;
        }

        if ($this->isDateAttribute($key)) {
            return $this->fromDateTime($attribute) === $this->fromDateTime($original);
        }

        if ($this->hasCast($key, ['object', 'collection'])) {
            return $this->fromJson($attribute) === $this->fromJson($original);
        }

        if ($this->hasCast($key, ['real', 'float', 'double'])) {
            if ($original === null) {
                return false;
            }
            return abs($this->castAttribute($key, $attribute) - $this->castAttribute($key, $original)) < PHP_FLOAT_EPSILON * 4;
        }

        if ($this->hasCast($key, static::$primitiveCastTypes)) {
            return $this->castAttribute($key, $attribute) ===
                $this->castAttribute($key, $original);
        }

        return is_numeric($attribute) && is_numeric($original)
            && strcmp((string) $attribute, (string) $original) === 0;
    }
    protected function isRelation(string $key): bool
    {
        return method_exists($this, $key) ||
            $this->relationResolver(static::class, $key);
    }
    protected function getRelationValue(string $key): Model|Collection
    {
        $result = $this->getRelationResult($key);

        return $result->hasItem() ? $result->getItem() : $result->getItems();
    }
    protected function getRelationshipFromMethod(string $method): RelationResult
    {
        if ($this->isRelation($method)) {
            $relation = $this->$method();
            if ($relation instanceof Relation) {

                $results = $relation->getResults();

                $this->setRelation($method, $results);

                return $results;
            }
        }

        throw new LogicException(static::class."::{$method} must return a relationship instance.");
    }
    protected function getRelationResult(string $key): RelationResult
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        return $this->getRelationshipFromMethod($key);
    }
}
