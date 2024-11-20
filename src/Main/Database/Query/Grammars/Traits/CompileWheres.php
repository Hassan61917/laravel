<?php

namespace Src\Main\Database\Query\Grammars\Traits;

use Src\Main\Database\Query\JoinClause;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Database\Query\WhereClause;

trait CompileWheres
{
    protected function compileWheres(QueryBuilder $query, array $wheres): string
    {
        if (empty($wheres)) {
            return "";
        }

        $result = [];

        foreach ($wheres as $where) {
            $result[] = $this->compileWhere($where);
        }

        return $this->concatenateWhereClause(implode($result), 'where');
    }
    protected function compileWhere(WhereClause $where): string
    {
        $method = "where{$where->getType()}";

        return "{$where->getBoolean()} {$this->$method($where)} ";
    }
    protected function whereBasic(WhereClause $where): string
    {
        $value = $this->parameter($where->getValue());

        return $this->wrap($where->getColumn())
            . ' ' . $where->getOperator()
            . ' ' . $value;
    }
    protected function whereColumn(WhereClause $where): string
    {
        return $this->wrap($where->getColumn())
            . ' ' . $where->getOperator()
            . ' ' . $this->wrap($where->getValue());
    }
    protected function whereIn(WhereClause $where): string
    {
        $values = $where->getValue();

        if (count($values)) {
            return $this->wrap($where->getColumn()) . ' in (' . $this->parseParameters($values) . ')';
        }

        return '0 = 1';
    }
    protected function whereNotIn(WhereClause $where): string
    {
        $values = $where->getValue();

        if (count($values)) {
            return $this->wrap($where->getColumn()) . ' not in (' . $this->parseParameters($values) . ')';
        }

        return '1 = 1';
    }
    protected function whereNull(WhereClause $where): string
    {
        return $this->wrap($where->getColumn()) . ' is null';
    }
    protected function whereNotNull(WhereClause $where): string
    {
        return $this->wrap($where->getColumn()) . ' is not null';
    }
    protected function whereExists(WhereClause $where): string
    {
        return 'exists (' . $this->compileSelect($where->getValue()) . ')';
    }
    protected function whereNotExists(WhereClause $where): string
    {
        return 'not exists (' . $this->compileSelect($where->getValue()) . ')';
    }
    protected function whereNested(WhereClause $where): string
    {
        $value = $where->getValue();

        $offset = $value instanceof JoinClause ? 3 : 6;

        return '(' . substr($this->compileWheres($value, $value->wheres), $offset) . ')';
    }
    protected function concatenateWhereClause(string $sql, string $conj): string
    {
        return " {$conj} {$this->removeLeadingBoolean($sql)} ";
    }
    protected function removeLeadingBoolean(string $value): string
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }
}
