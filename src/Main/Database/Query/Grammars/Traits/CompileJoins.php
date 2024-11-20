<?php

namespace Src\Main\Database\Query\Grammars\Traits;

use Src\Main\Database\Query\JoinClause;
use Src\Main\Database\Query\QueryBuilder;

trait CompileJoins
{
    protected function compileJoins(QueryBuilder $query, array $joins): string
    {
        $res = [];

        foreach ($joins as $join) {
            $res[] = $this->compileJoin($join) .
                $this->removeLeadingBoolean($this->compileWhere($join));
        }

        return implode(' ', $res);
    }
    protected function compileJoin(JoinClause $join): string
    {
        $table = $this->wrapTable($join->getTable());

        return trim("{$join->getMethod()} join {$table} {$join->conjunction}");
    }
}
