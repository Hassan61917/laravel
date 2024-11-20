<?php

namespace Src\Main\Database\Query\Traits;

use Closure;
use InvalidArgumentException;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Database\Query\WhereClause;

trait WithWheres
{
    public array $wheres = [];
    public function where(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->createWhere($column, $operator, $value, "Basic", $boolean);
    }
    public function orWhere(string $column, string $operator = null, mixed $value = null): static
    {
        return $this->where($column, $operator, $value, 'or');
    }
    public function whereNot(string $column, string $operator = null, mixed $value = null, string $boolean = 'and'): static
    {
        return $this->where($column, $operator, $value, $boolean . ' not');
    }
    public function orWhereNot(string $column, string $operator = null, mixed $value = null): static
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }
    public function whereColumn(string $column, string $operator = null, mixed $value = null, $boolean = 'and'): static
    {
        return $this->createWhere($column, $operator, $value, "Column", $boolean);
    }
    public function orWhereColumn(string $column, string $operator = null, mixed $value = null): static
    {
        return $this->whereColumn($column, $operator, $value, 'or');
    }
    public function whereIn(string $column, array $values, string $boolean = 'and', bool $not = false): static
    {
        $type = $not ? 'NotIn' : 'In';

        $clause = $this->createWhereClause($column, null, $values, $type, $boolean);

        $this->addWhere($clause);

        $this->addBinding('where', array_values($values));

        return $this;
    }
    public function orWhereIn(string $column, array $values): static
    {
        return $this->whereIn($column, $values, 'or');
    }
    public function whereNotIn(string $column, array $values, string $boolean = 'and'): static
    {
        return $this->whereIn($column, $values, $boolean, true);
    }
    public function whereNull(array $columns, string $boolean = 'and', bool $not = false): static
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach ($columns as $column) {
            $clause = $this->createWhereClause($column, null, null, $type, $boolean);
            $this->addWhere($clause);
        }

        return $this;
    }
    public function orWhereNull(array $column): static
    {
        return $this->whereNull($column, 'or');
    }
    public function whereNotNull(array $columns, string $boolean = 'and'): static
    {
        return $this->whereNull($columns, $boolean, true);
    }
    public function whereExists(QueryBuilder $query, string $boolean = 'and', bool $not = false): static
    {
        return $this->addWhereExistsQuery($query, $boolean, $not);
    }
    public function orWhereExists(QueryBuilder $query, bool $not = false): static
    {
        return $this->whereExists($query, 'or', $not);
    }
    public function whereNotExists(QueryBuilder $query, string $boolean = 'and'): static
    {
        return $this->whereExists($query, $boolean, true);
    }
    public function addWhereExistsQuery(QueryBuilder $query, string $boolean = 'and', bool $not = false): static
    {
        if ($query !== $this) {
            $type = $not ? 'NotExists' : 'Exists';

            $this->createWhere(null, null, $query, $type, $boolean);

            $this->addBinding('where', $query->getBindings());
        }
        return $this;
    }
    public function mergeWheres(array $wheres, array $bindings): static
    {
        $this->wheres = array_merge($this->wheres, $wheres);

        $this->bindings['where'] = array_values(array_merge($this->bindings['where'], $bindings));

        return $this;
    }
    public function whereNested(Closure $callback, string $boolean = 'and'): static
    {
        $query = $this->forNestedWhere();

        $callback($query);

        return $this->addNestedWhereQuery($query, $boolean);
    }
    public function forNestedWhere(): static
    {
        return $this->newQuery()->from($this->from);
    }
    public function addNestedWhereQuery(QueryBuilder $query, string $boolean = 'and'): static
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] =  $this->createWhereClause(null, null, $query, $type, $boolean);

            $this->addBinding("where", $query->bindings["where"]);
        }

        return $this;
    }
    protected function createWhere(?string $column, ?string $operator, mixed $value, string $type, string $boolean): QueryBuilder
    {
        [$operator, $value] = $this->prepareValueAndOperator($operator, $value);

        $clause = $this->createWhereClause($column, $operator, $value, $type, $boolean);

        $this->addWhere($clause);

        $this->addBinding('where', $value);

        return $this;
    }
    protected function prepareValueAndOperator(?string $operator = null, mixed $value = null): array
    {
        if (is_null($value)) {
            return ["=", $operator];
        }
        if (!$this->isOperatorValid($operator)) {
            throw new InvalidArgumentException("Invalid operator: {$operator}.");
        }
        return [$operator, $value];
    }
    protected function isOperatorValid(string $operator): string
    {
        $operator = strtolower($operator);

        return in_array($operator, $this->operators) ||
            in_array($operator, $this->grammar->getOperators());
    }
    public function createWhereClause(?string $column, ?string $operator, mixed $value, string $type = "Column", string $boolean = "and"): WhereClause
    {
        $clause = new WhereClause($column, $value);
        $clause->setType($type);
        $clause->setOperator($operator);
        $clause->setBoolean($boolean);
        return $clause;
    }
    protected function addWhere(WhereClause $where): static
    {
        $this->wheres[] = $where;

        return $this;
    }
}
