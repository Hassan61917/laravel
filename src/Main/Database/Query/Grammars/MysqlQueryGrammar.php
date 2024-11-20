<?php

namespace Src\Main\Database\Query\Grammars;

class MysqlQueryGrammar extends QueryGrammar
{
    public function compileRandom(?int $seed): string
    {
        $sd = is_null($seed) ? "" : "'{$seed}'";
        return "RAND({$sd})";
    }
    protected function wrapValue(string $value): string
    {
        return $value === '*' ? $value : '`' . str_replace('`', '``', $value) . '`';
    }
}
