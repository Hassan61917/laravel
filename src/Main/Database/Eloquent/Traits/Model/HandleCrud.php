<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\EloquentBuilder;

trait HandleCrud
{
    protected static function query(): EloquentBuilder
    {
        return (new static())->newQuery();
    }
    public static function all(array $columns = ['*']): Collection
    {
        return static::query()->get($columns);
    }
    public static function destroy(string ...$ids): void
    {
        if (empty($ids)) {
            return;
        }

        $instance = new static();

        $models = $instance->whereIn($instance->getKeyName(), $ids)->get();

        foreach ($models as $model) {
            $model->delete();
        }
    }
    public function save(): bool
    {
        $query = $this->newModelQuery();

        $this->fireModelEvent('saving');

        if ($this->exists) {
            $saved = $this->performUpdate($query);
        } else {
            $saved = $this->performInsert($query);
        }

        if ($saved) {
            $this->finishSave();
        }

        return false;
    }
    public function update(array $attributes = []): bool
    {
        if (!$this->exists) {
            return false;
        }

        return $this->fill($attributes)->save();
    }
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }
        $this->fireModelEvent('deleting');

        $this->touchOwners();

        $this->performDelete();

        $this->fireModelEvent('deleted');

        return true;
    }
    public function increment(string $column, int $amount = 1): int
    {
        return $this->incrementOrDecrement($column, $amount, 'increment');
    }
    public function decrement(string $column, int $amount = 1): int
    {
        return $this->incrementOrDecrement($column, $amount, 'decrement');
    }
    protected function performInsert(EloquentBuilder $query): bool
    {
        if ($this->usesUniqueIds()) {
            $this->setUniqueIds();
        }

        $this->fireModelEvent('creating');

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $attributes = $this->attributes;

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        } else {
            $query->insert($attributes);
        }

        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created');

        return true;
    }
    protected function insertAndSetId(EloquentBuilder $query, array $attributes): void
    {
        $keyName = $this->getKeyName();

        $id = $query->insertGetId($attributes, $keyName);

        $this->setAttribute($keyName, $id);
    }
    protected function performUpdate(EloquentBuilder $query): bool
    {
        if ($this->isClean()) {
            return true;
        }

        $this->fireModelEvent('updating');

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirty();

        $this->setKeysForSave($query)->update($dirty);

        $this->syncChanges();

        $this->fireModelEvent('updated');

        return true;
    }
    protected function setKeysForSave(EloquentBuilder $query): EloquentBuilder
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSave());

        return $query;
    }
    protected function getKeyForSave(): string
    {
        return $this->original[$this->getKeyName()] ?? $this->getKey();
    }
    protected function performDelete(): void
    {
        $this->setKeysForSave($this->newModelQuery())->delete();

        $this->exists = false;
    }
    protected function incrementOrDecrement(string $column, int $amount, string $method): int
    {
        if (!$this->exists) {
            return 0;
        }

        $this->{$column} += ($method === 'increment' ? $amount : $amount * -1);

        $result = $this->setKeysForSave($this->newQueryWithoutScopes())->{$method}($column, $amount, $this->updateTimestampColumns());

        $this->fireModelEvent('updating');

        $this->syncChanges();

        $this->fireModelEvent('updated');

        $this->syncOriginalAttributes($column);

        return $result;
    }
    protected function finishSave(): void
    {
        $this->fireModelEvent('saved');

        if ($this->isDirty()) {
            $this->touchOwners();
        }

        $this->syncOriginal();
    }
}
