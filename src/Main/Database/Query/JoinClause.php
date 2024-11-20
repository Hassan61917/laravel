<?php

namespace Src\Main\Database\Query;

class JoinClause extends WhereClause
{
    public string $conjunction = "on";
    public function __construct(
        protected string $table,
        protected string $method,
        string $column,
        mixed $value,
    ) {
        parent::__construct($column, $value);
    }
    public function getTable(): string
    {
        return $this->table;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
}
