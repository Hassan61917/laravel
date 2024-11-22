<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\MorphMany;
use Src\Main\Database\Eloquent\Relations\MorphOne;
use Src\Main\Database\Eloquent\Relations\MorphTo;
use Src\Main\Database\Eloquent\Relations\Relation;
use Src\Main\Database\Exceptions\Eloquent\ClassMorphViolationException;
use Src\Main\Utils\Str;

trait HandleMorph
{
    public static array $manyMethods = [
        'belongsToMany',
        'morphToMany',
        'morphedByMany',
    ];
    public function getMorphClass(): string
    {
        $morphMap = Relation::morphMap();

        if (count($morphMap) && in_array(static::class, $morphMap)) {
            return array_search(static::class, $morphMap, true);
        }

        if (Relation::requiresMorphMap()) {
            throw new ClassMorphViolationException(get_class($this));
        }

        return static::class;
    }
    public function morphOne(string $related, string $name, ?string $type = null, ?string $id = null, ?string $localKey = null): MorphOne
    {
        [$instance, $type, $id, $table, $localKey] = $this->getMorphParameters($related, $name, $type, $id, $localKey);

        return $this->newMorphOne(
            $instance->newQuery(),
            $this,
            $table . '.' . $type,
            $table . '.' . $id,
            $localKey
        );
    }
    public function morphMany(string $related, string $name, ?string $type = null, ?string $id = null, ?string $localKey = null): MorphMany
    {
        [$instance, $type, $id, $table, $localKey] = $this->getMorphParameters($related, $name, $type, $id, $localKey);

        return $this->newMorphMany(
            $instance->newQuery(),
            $this,
            $table . '.' . $type,
            $table . '.' . $id,
            $localKey
        );
    }
    public function morphTo(?string $name = null, ?string $type = null, ?string $id = null, ?string $ownerKey = null): MorphTo
    {
        $name = $name ?: $this->guessBelongsToRelation();

        [$type, $id] = $this->getMorphs(Str::snake($name), $type, $id);

        $class = $this->getAttributeFromArray($type);

        return is_null($class) || $class === ''
            ? $this->morphEagerTo($name, $type, $id, $ownerKey)
            : $this->morphInstanceTo($class, $name, $type, $id, $ownerKey);
    }
    protected function getMorphParameters(string $related, string $name, ?string $type, ?string $id, ?string $localKey): array
    {
        $instance = $this->newRelatedInstance($related);

        [$type, $id] = $this->getMorphs($name, $type, $id);

        $table = $instance->getTable();

        $localKey = $localKey ?: $this->getKeyName();

        return [$instance, $type, $id, $table, $localKey];
    }
    protected function getMorphs(string $name, ?string $type, ?string $id): array
    {
        return [$type ?: $name . '_type', $id ?: $name . '_id'];
    }
    protected function newMorphOne(EloquentBuilder $query, Model $parent, string $type, string $id, string $localKey): MorphOne
    {
        return new MorphOne($query, $parent, $type, $id, $localKey);
    }
    protected function newMorphMany(EloquentBuilder $query, Model $parent, string $type, string $id, string $localKey): MorphMany
    {
        return new MorphMany($query, $parent, $type, $id, $localKey);
    }
    protected function morphEagerTo(string $name, string $type, string $id, ?string $ownerKey): MorphTo
    {
        return $this->newMorphTo(
            $this->newQuery()->setEagerLoads([]),
            $this,
            $id,
            $ownerKey,
            $type,
            $name
        );
    }
    protected function newMorphTo(EloquentBuilder $query, Model $parent, string $foreignKey, ?string $ownerKey, string $type, string $relation): MorphTo
    {
        $ownerKey = $ownerKey ?: $query->getModel()->getKeyName();

        return new MorphTo($query, $parent, $foreignKey, $ownerKey, $type, $relation);
    }
    protected function morphInstanceTo(string $target, string $name, string $type, string $id, ?string $ownerKey): MorphTo
    {
        $instance = $this->newRelatedInstance(
            static::getActualClassNameForMorph($target)
        );

        return $this->newMorphTo(
            $instance->newQuery(),
            $this,
            $id,
            $ownerKey,
            $type,
            $name
        );
    }
}
