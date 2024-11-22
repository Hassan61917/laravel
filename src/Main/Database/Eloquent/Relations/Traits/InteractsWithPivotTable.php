<?php

namespace Src\Main\Database\Eloquent\Relations\Traits;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\Pivot;
use Src\Main\Database\Query\QueryBuilder;

trait InteractsWithPivotTable
{
    public function newPivotStatement(): QueryBuilder
    {
        return $this->query
            ->getQuery()
            ->newQuery()
            ->from($this->table);
    }
    public function newPivotQuery(): QueryBuilder
    {
        $query = $this->newPivotStatement();

        foreach ($this->pivotWheres as $arguments) {
            $query->where(...$arguments);
        }

        foreach ($this->pivotWhereIns as $arguments) {
            $query->whereIn(...$arguments);
        }

        foreach ($this->pivotWhereNulls as $arguments) {
            $query->whereNull(...$arguments);
        }

        return $query->where($this->getQualifiedForeignPivotKeyName(), $this->parent->{$this->parentKey});
    }
    public function newPivotStatementForId(Model|int $id): QueryBuilder
    {
        return $this->newPivotQuery()->where($this->relatedPivotKey, $this->parseId($id));
    }
    public function hasPivotColumn(string $column): bool
    {
        return in_array($column, $this->pivotColumns);
    }
    public function newPivot(array $attributes = [], bool $exists = false): Pivot
    {
        $attributes = array_merge(
            array_column($this->pivotValues, 'value', 'column'),
            $attributes
        );

        $pivot = $this->related->newPivot(
            $this->parent,
            $attributes,
            $this->table,
            $exists,
        );

        return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
    }
    public function newExistingPivot(array $attributes = []): Pivot
    {
        return $this->newPivot($attributes, true);
    }
    public function withPivot(array $columns): static
    {
        $this->pivotColumns = array_merge(
            $this->pivotColumns,
            $columns
        );

        return $this;
    }
    public function attach(Model|int $id, array $attributes = [], bool $touch = true): void
    {
        $this->newPivotStatement()->insert(
            $this->formatAttachRecords($this->parseId($id), $attributes)
        );

        if ($touch) {
            $this->touchIfTouching();
        }
    }
    public function attachAll(array $ids, array $attributes = [], bool $touch = true): void
    {
        foreach ($ids as $id) {
            $this->attach($id, $attributes, $touch);
        }
    }
    public function detach(Model|int $id, bool $touch = true): int
    {
        $query = $this->newPivotQuery();

        $id = $this->parseId($id);

        $query->where($this->getQualifiedRelatedPivotKeyName(), $id);

        $results = $query->delete();

        if ($touch) {
            $this->touchIfTouching();
        }

        return $results;
    }
    public function detachAll(array $ids, bool $touch = true): void
    {
        foreach ($ids as $id) {
            $this->detach($id, $touch);
        }
    }
    public function updateExistingPivot(Model|int $id, array $attributes, bool $touch = true): int
    {
        if ($this->hasPivotColumn($this->updatedAt())) {
            $attributes = $this->addTimestampsToAttachment($attributes, true);
        }

        $updated = $this->newPivotStatementForId($this->parseId($id))
            ->update($this->castAttributes($attributes));

        if ($touch) {
            $this->touchIfTouching();
        }

        return $updated;
    }
    public function toggle(Model|int $id, bool $touch = true): array
    {
        $changes = [
            'attached' => [],
            'detached' => [],
        ];

        $records = [$this->parseId($id)];

        $detach = array_values(array_intersect(
            $this->newPivotQuery()->pluck($this->relatedPivotKey)->all(),
            $records
        ));

        if (count($detach) > 0) {
            $this->detachAll($detach);
            $changes['detached'] = $this->castKeys($detach);
        }

        $attach = array_diff($records, $detach);

        if (count($attach) > 0) {
            $this->attachAll($attach);
            $changes['attached'] = array_keys($attach);
        }

        if ($touch && (count($changes['attached']) ||
            count($changes['detached']))) {
            $this->touchIfTouching();
        }

        return $changes;
    }
    public function sync(Model|int $id, array $attributes = [], bool $detaching = true): array
    {
        $changes = [
            'attached' => [],
            'detached' => [],
            'updated' => [],
        ];

        $current = $this->getCurrentlyAttachedPivots()
            ->pluck($this->relatedPivotKey)->all();

        $records = [$this->parseId($id)];

        if ($detaching) {
            $detach = array_diff($current, $records);

            if (count($detach) > 0) {
                $this->detachAll($detach);
                $changes['detached'] = $this->castKeys($detach);
            }
        }

        $changes = array_merge(
            $changes,
            $this->attachNew($records, $current, $attributes, false)
        );

        if (
            count($changes['attached']) ||
            count($changes['updated']) ||
            count($changes['detached'])
        ) {
            $this->touchIfTouching();
        }

        return $changes;
    }
    public function syncWithoutDetaching(Model|int $id): array
    {
        return $this->sync($id, [], false);
    }
    protected function parseId(Model|int $value): int
    {
        return $value instanceof Model
            ? $value->{$this->relatedKey}
            : $value;
    }
    protected function formatAttachRecords(int $id, array $attributes): array
    {
        $hasTimestamps = ($this->hasPivotColumn($this->createdAt()) ||
            $this->hasPivotColumn($this->updatedAt()));

        return $this->formatAttachRecord(
            $id,
            $attributes,
            $hasTimestamps
        );
    }
    protected function formatAttachRecord(int $value, array $attributes, bool $hasTimestamps): array
    {
        [$id, $attributes] = $this->extractAttachIdAndAttributes($value, $attributes);

        return array_merge(
            $this->baseAttachRecord($id, $hasTimestamps),
            $this->castAttributes($attributes)
        );
    }
    protected function extractAttachIdAndAttributes(int $value, array $attributes): array
    {
        return [$value, $attributes];
    }
    protected function baseAttachRecord(int $id, bool $timed): array
    {
        $record[$this->relatedPivotKey] = $id;

        $record[$this->foreignPivotKey] = $this->parent->{$this->parentKey};

        if ($timed) {
            $record = $this->addTimestampsToAttachment($record);
        }

        foreach ($this->pivotValues as $value) {
            $record[$value['column']] = $value['value'];
        }

        return $record;
    }
    protected function addTimestampsToAttachment(array $record, bool $exists = false): array
    {
        $fresh = $this->parent->freshTimestamp();

        if (! $exists && $this->hasPivotColumn($this->createdAt())) {
            $record[$this->createdAt()] = $fresh;
        }

        if ($this->hasPivotColumn($this->updatedAt())) {
            $record[$this->updatedAt()] = $fresh;
        }

        return $record;
    }
    protected function castAttributes(array $attributes): array
    {
        return $attributes;
    }
    protected function castKeys(array $keys): array
    {
        return array_map(fn($v) => $this->castKey($v), $keys);
    }
    protected function castKey(int $key): float|int|string
    {
        return $this->getTypeSwapValue($this->related->getKeyType(), $key);
    }
    protected function getTypeSwapValue(string $type, int $value): float|int|string
    {
        return match (strtolower($type)) {
            'real', 'float', 'double' => (float) $value,
            'string' => (string) $value,
            default => $value,
        };
    }
    protected function getCurrentlyAttachedPivots(): Collection
    {
        return $this->newPivotQuery()->get()->map(function ($record) {
            $pivot = Pivot::fromRawAttributes($this->parent, (array) $record, $this->getTable(), true);

            return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
        });
    }
    protected function attachNew(array $records, array $current, array $attributes, bool $touch = true): array
    {
        $changes = ['attached' => [], 'updated' => []];

        foreach ($records as $id) {
            if (! in_array($id, $current)) {
                $this->attach($id, $attributes, $touch);
                $changes['attached'][] = $this->castKey($id);
            } elseif ($this->updateExistingPivot($id, $attributes, $touch)) {
                $changes['updated'][] = $this->castKey($id);
            }
        }

        return $changes;
    }
}
