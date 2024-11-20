<?php

namespace Src\Main\Database\Query\Grammars;

use Src\Main\Database\BaseGrammar;
use Src\Main\Database\Query\Grammars\Traits\CompileCrud;
use Src\Main\Database\Query\Grammars\Traits\CompileJoins;
use Src\Main\Database\Query\Grammars\Traits\CompileWheres;
use Src\Main\Database\Query\QueryBuilder;

abstract class QueryGrammar extends BaseGrammar
{
    use CompileWheres,
        CompileJoins,
        CompileCrud;

    protected array $operators = [];
    protected array $selectComponents = [
        "aggregate",
        "columns",
        'from',
        "joins",
        "wheres",
        "groups",
        "orders",
        'limit',
        'offset',
    ];
    public function getOperators(): array
    {
        return $this->operators;
    }
    public function compileSelect(QueryBuilder $query): string
    {
        $components = $this->compileComponents($query);

        $result = $this->concatenate($components);

        return trim($result);
    }
    public function compileRandom(?int $seed): string
    {
        return 'RANDOM()';
    }
    public function compileExists(QueryBuilder $query): string
    {
        $select = $this->compileSelect($query);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }
    public function compileTruncate(QueryBuilder $query): array
    {
        return ['truncate table ' . $this->wrapTable($query->from) => []];
    }
    protected function compileComponents(QueryBuilder $query): array
    {
        $sql = [];
        foreach ($this->selectComponents as $component) {
            if (isset($query->$component)) {
                $method = "compile" . ucfirst($component);
                $sql[] = $this->$method($query, $query->$component);
            }
        }
        return $sql;
    }
    protected function compileFrom(QueryBuilder $query, string $table): string
    {
        return 'from ' . $this->wrapTable($table);
    }
    protected function compileColumns(QueryBuilder $query, array $columns): string
    {
        if (isset($query->aggregate)) {
            return "";
        }

        return "select " . $this->parseColumns($columns);
    }
    protected function compileGroups(QueryBuilder $query, array $groups): string
    {
        return 'group by ' . $this->parseColumns($groups);
    }
    protected function compileOrders(QueryBuilder $query, array $orders): string
    {
        return 'order by ' . implode(', ', $this->compileOrdersToArray($query, $orders));
    }
    protected function compileOrdersToArray(QueryBuilder $query, array $orders): array
    {
        return array_map(function ($order) {
            return $order['sql'] ?? $this->wrap($order['column']) . ' ' . $order['direction'];
        }, $orders);
    }
    protected function compileLimit(QueryBuilder $query, int  $limit): string
    {
        return 'limit ' . $limit;
    }
    protected function compileOffset(QueryBuilder $query, int  $limit): string
    {
        return "offset {$limit}";
    }
    protected function compileAggregate(QueryBuilder $query, array $aggregate): string
    {
        [$fn, $columns] = array_values($aggregate);

        $column = $this->parseColumns($columns);

        if ($query->distinct) {
            $column = 'distinct ' . $column;
        }

        return 'select ' . $fn . '(' . $column . ') as aggregate';
    }
    protected function concatenate(array $segments): string
    {
        return implode(' ', array_filter($segments, fn($value) => (string) $value !== ''));
    }
}
