<?php

namespace Src\Main\Database\Eloquent\Relations\Traits;

use Src\Main\Database\Eloquent\Model;
trait AsPivot
{
    public Model $pivotParent;
    public string $foreignKey;
    public string $relatedKey;
    public static function fromAttributes(Model $parent,array $attributes,string $table,bool $exists = false): static
    {
        $instance = new static();

        $instance->timestamps = $instance->hasTimestampAttributes($attributes);

        $instance
            ->setConnectionName($parent->getConnectionName())
            ->setTable($table)
            ->forceFill($attributes)
            ->syncOriginal();

        $instance->pivotParent = $parent;

        $instance->exists = $exists;

        return $instance;
    }
    public static function fromRawAttributes(Model $parent,array $attributes,string $table,bool $exists = false): static
    {
        $instance = static::fromAttributes($parent, [], $table, $exists);

        $instance->timestamps = $instance->hasTimestampAttributes($attributes);

        $instance->setRawAttributes(
            array_merge($instance->getRawOriginal(), $attributes), $exists
        );

        return $instance;
    }
    public function hasTimestampAttributes(array $attributes = []): bool
    {
        $attributes = count($attributes) > 0 ? $attributes : $this->attributes;
        return array_key_exists($this->getCreatedAtColumn(), $attributes);
    }
    public function setPivotKeys(string $foreignKey,string $relatedKey):static
    {
        $this->foreignKey = $foreignKey;

        $this->relatedKey = $relatedKey;

        return $this;
    }
}