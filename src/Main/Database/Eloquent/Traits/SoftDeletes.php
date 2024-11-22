<?php

namespace Src\Main\Database\Eloquent\Traits;

use Src\Main\Database\Eloquent\Scopes\SoftDeletingScope;

trait SoftDeletes
{
    protected bool $forceDeleting = false;
    const DELETED_AT = 'deleted_at';
    public function getDeletedAtColumn(): string
    {
        return self::DELETED_AT;
    }
    public function isForceDeleting(): bool
    {
        return $this->forceDeleting;
    }
    public function getQualifiedDeletedAtColumn(): string
    {
        return $this->qualifyColumn($this->getDeletedAtColumn());
    }
    public static function bootSoftDeletes(): void
    {
        self::addScope();
    }
    public function initializeSoftDeletes(): void
    {
        $deletedAt = $this->getDeletedAtColumn();

        if (!$this->hasCast($deletedAt)) {
            $this->casts[$deletedAt] = 'datetime';
        }
    }
    public function forceDelete(): void
    {
        $this->forceDeleting = true;

        $this->delete();

        $this->forceDeleting = false;
    }
    public function restore(): bool
    {
        $this->{$this->getDeletedAtColumn()} = null;

        $this->exists = true;

        return $this->save();
    }
    public function trashed(): bool
    {
        return $this->{$this->getDeletedAtColumn()} != null;
    }
    protected static function addScope(): void
    {
        $scope = new SoftDeletingScope();

        $class = get_class($scope);

        static::addGlobalScope($class, $scope);
    }
    protected function performDelete(): void
    {
        if ($this->forceDeleting || !property_exists($this, $this->getDeletedAtColumn())) {
            $this->setKeysForSave($this->newModelQuery())->forceDelete();

            $this->exists = false;
        } else {
            $this->runSoftDelete();
        }
    }
    protected function runSoftDelete(): void
    {
        $query = $this->setKeysForSave($this->newModelQuery());

        $time = $this->freshTimestamp();

        $this->{$this->getDeletedAtColumn()} = $time;

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $columns = array_merge($columns(), $this->updateTimestampColumns());

        $query->update($columns);

        $this->syncOriginalAttributes(...array_keys($columns));
    }
}
