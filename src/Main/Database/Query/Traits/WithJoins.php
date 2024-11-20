<?php

namespace Src\Main\Database\Query\Traits;

use Src\Main\Database\Query\JoinClause;

trait WithJoins
{
    public array $joins;
    public function join(string $table, string $first, string $operator = null, string $second = null, string $method = 'inner', bool $where = false): static
    {
        $join = $this->createJoinClause($table, $method, $first, $operator, $second, $where);

        if ($where) {
            $this->addBinding('where', $second);
        }

        $this->joins[] = $join;

        $this->addBinding('join', $this->getBindings());

        return $this;
    }
    public function joinWhere(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner'): static
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }
    public function leftJoin(string $table, string $first, string $operator = null, string $second = null): static
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }
    public function leftJoinWhere(string $table, string $first, string $operator = null, string $second = null): static
    {
        return $this->joinWhere($table, $first, $operator, $second, 'left');
    }
    public function rightJoin(string $table, string $first, string $operator = null, string $second = null): static
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }
    public function rightJoinWhere(string $table, string $first, string $operator = null, string $second = null): static
    {
        return $this->joinWhere($table, $first, $operator, $second, 'right');
    }
    public function crossJoin(string $table, string $first, string $operator = null, string $second = null): static
    {
        return $this->join($table, $first, $operator, $second, 'cross');
    }
    protected function createJoinClause(string $table, string $method, string $column, string $operator, string $value, bool $where = false): JoinClause
    {
        $join = new JoinClause($table, $method, $column, $value);

        $join->setOperator($operator);

        if ($where) {
            $join->setType("Basic")
                ->setConjunction("where");
        }

        return $join;
    }
}
