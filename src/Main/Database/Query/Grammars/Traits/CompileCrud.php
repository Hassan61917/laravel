<?php

namespace Src\Main\Database\Query\Grammars\Traits;

use Illuminate\Support\Arr;
use Src\Main\Database\Query\QueryBuilder;

trait CompileCrud
{
    public function compileInsert(QueryBuilder $query, array $values): string
    {
        $table = $this->wrapTable($query->from);

        $columns = $this->parseColumns(array_keys($values));

        $parameters = $this->parseParameters(array_values($values));

        return "insert into $table ($columns) values ($parameters)";
    }
    public function compileUpdate(QueryBuilder $query, array $values,array $raw=[]): string
    {
        $table = $this->wrapTable($query->from);

        $columns = $this->compileUpdateColumns($query, $values,$raw);

        $where = $this->compileWheres($query, $query->wheres);

        $sql = isset($query->joins)
            ? $this->compileUpdateWithJoins($query, $table, $columns, $where)
            : $this->compileUpdateWithoutJoins($query, $table, $columns, $where);

        return trim($sql);
    }
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        $cleanBindings = Arr::except($bindings, ['select', 'join']);

        $values = Arr::flatten($values);

        return array_values(
            array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
        );
    }
    public function compileDelete(QueryBuilder $query): string
    {
        $table = $this->wrapTable($query->from);

        $where = $this->compileWheres($query, $query->wheres);

        $sql = isset($query->joins)
            ? $this->compileDeleteWithJoins($query, $table, $where)
            : $this->compileDeleteWithoutJoins($query, $table, $where);


        return trim($sql);
    }
    public function prepareBindingsForDelete(array $bindings): array
    {
        return Arr::flatten(Arr::except($bindings, ['select']));
    }
    protected function compileUpdateColumns(QueryBuilder $query, array $values,array $raw = []): string
    {
        $result = [];

        foreach ($raw as $key => $value) {
          $result[] = "{$this->wrap($key)} = $value";
        }

        foreach ($values as $key => $value) {
            $result[] = "{$this->wrap($key)} = {$this->parameter($value)}";
        }

        return implode(", ", $result);
    }
    protected function compileUpdateWithoutJoins(QueryBuilder $query, string $table, string $columns, string $where): string
    {
        return "update {$table} set {$columns} {$where}";
    }
    protected function compileUpdateWithJoins(QueryBuilder $query, string $table, string $columns, string $where): string
    {
        $joins = $this->compileJoins($query, $query->joins);

        return "update {$table} {$joins} set {$columns} {$where}";
    }
    protected function compileDeleteWithoutJoins(QueryBuilder $query, $table, $where): string
    {
        return "delete from {$table} {$where}";
    }
    protected function compileDeleteWithJoins(QueryBuilder $query, string $table, string $where): string
    {
        $array = explode(' as ', $table);

        $alias = array_pop($array);

        $joins = $this->compileJoins($query, $query->joins);

        return "delete {$alias} from {$table} {$joins} {$where}";
    }
}
