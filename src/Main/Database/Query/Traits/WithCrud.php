<?php

namespace Src\Main\Database\Query\Traits;

use Closure;
use Illuminate\Support\Collection;

trait WithCrud
{
    public function insert(array $values): bool
    {
        if (empty($values)) {
            return true;
        }

        $this->applyBeforeQueryCallbacks();

        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            array_values($values)
        );
    }
    public function insertGetId(array $values, string $sequence = null): int
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileInsert($this, $values);

        $values = array_values($values);

        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }
    public function update(array $values, array $raw = []): int
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->grammar->compileUpdate($this, $values, $raw);

        return $this->connection->update(
            $sql,
            array_values($this->grammar->prepareBindingsForUpdate($this->bindings, $values))
        );
    }
    public function delete(int $id = null): int
    {
        if ($id) {
            $this->where($this->from . '.id', '=', $id);
        }

        $this->applyBeforeQueryCallbacks();

        return $this->connection->delete(
            $this->grammar->compileDelete($this),
            array_values($this->grammar->prepareBindingsForDelete($this->bindings))
        );
    }
    public function increment(string $column, int $amount = 1, array $extra = []): int
    {
        return $this->resolveIncrement($column, $amount, true, $extra);
    }
    public function decrement(string $column, int $amount = 1, array $extra = []): int
    {
        return $this->resolveIncrement($column, $amount, false, $extra);
    }
    public function get(array $columns = ['*']): Collection
    {
        $result = $this->onceWithColumns(
            $columns,
            fn() => $this->processor->processSelect($this, $this->runSelect())
        );

        return collect($result);
    }
    public function pluck(string $column, ?string $key = null): Collection
    {
        $queryResult = $this->onceWithColumns(
            is_null($key) ? [$column] : [$column, $key],
            fn() => $this->processor->processSelect($this, $this->runSelect())
        );

        if (empty($queryResult)) {
            return collect();
        }

        $column = $this->stripTableForPluck($column);

        $key = $this->stripTableForPluck($key);

        return is_array($queryResult[0])
            ? $this->pluckFromArrayColumn($queryResult, $column, $key)
            : $this->pluckFromObjectColumn($queryResult, $column, $key);
    }
    protected function onceWithColumns(array $columns, Closure $callback): array
    {
        $original = $this->columns ?? [];

        if (!isset($this->columns)) {
            $this->columns = $columns;
        }

        $result = $callback();

        $this->columns = $original;

        return $result;
    }
    protected function stripTableForPluck(?string $column): ?string
    {
        if (is_null($column)) {
            return null;
        }

        $columnString = $column;

        $separator = str_contains(strtolower($columnString), ' as ') ? ' as ' : '\.';

        return last(preg_split('~' . $separator . '~i', $columnString));
    }
    protected function pluckFromArrayColumn(array $queryResult, string $column, ?string $key): Collection
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row[$column];
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row[$key]] = $row[$column];
            }
        }

        return collect($results);
    }
    protected function pluckFromObjectColumn(array $queryResult, string $column, ?string $key): Collection
    {
        $results = [];

        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row->$column;
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row->$key] = $row->$column;
            }
        }

        return collect($results);
    }
}
