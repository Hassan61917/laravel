<?php

namespace Src\Main\Database\Schema;

use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Database\Schema\Blueprint\ColumnDefinition;

class ForeignIdColumnDefinition extends ColumnDefinition
{
    public function __construct(
        protected Blueprint $blueprint,
        array $attributes = []
    ) {
        parent::__construct($attributes);
    }
    public function constrained(string $table = null, string $column = 'id', string $indexName = null): ForeignIdColumnDefinition
    {
        return $this->references($column, $indexName)->on($table);
    }
    public function references(string $column, string $indexName = null): ForeignKeyDefinition
    {
        return $this->blueprint->foreign($this->name, $indexName)->references($column);
    }
}
