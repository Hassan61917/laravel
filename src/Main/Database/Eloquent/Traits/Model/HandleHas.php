<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use InvalidArgumentException;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\HasMany;
use Src\Main\Database\Eloquent\Relations\HasOne;

trait HandleHas
{
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        [$instance, $foreignKey, $localKey] = $this->createParameters($related, $foreignKey, $localKey);

        return new HasOne($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        [$instance, $foreignKey, $localKey] = $this->createParameters($related, $foreignKey, $localKey);

        return new HasMany($instance->newQuery(), $this, $instance->getTable() . '.' . $foreignKey, $localKey);
    }
    protected function createParameters(string $related, ?string $foreignKey, ?string $ownerKey): array
    {
        $instance = $this->newRelatedInstance($related);

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return [$instance, $foreignKey, $ownerKey];
    }
    protected function newRelatedInstance(string $class): Model
    {
        $instance = app()->make($class);

        if (!$instance instanceof Model) {
            throw new InvalidArgumentException("Class {$class} must be a instance of Model");
        }

        if (! $instance->getConnectionName()) {
            $instance->setConnectionName($this->connection);
        }

        return $instance;
    }
}
