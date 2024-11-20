<?php

namespace Src\Main\Database\Schema\Grammars;

use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Database\Schema\Fluent;

abstract class SqlSchemaGrammar extends SchemaGrammar
{
    protected array $types = [
        "bigInteger" => "bigint",
        "text" => "text",
        "double" => "double",
        "longText" => "longText",
        "boolean" => "tinyint(1)",
        "date" => "date",
    ];
    public function compileCreateDatabase(string $name, Connection $connection): string
    {
        $collation = $this->wrapValue($connection->getOption('collation'));

        $name = $this->wrapValue($name);

        return "create database {$name} default collate {$collation}";
    }
    public function compileDropDatabase(string $name): string
    {
        $name = $this->wrapValue($name);

        return "drop database if exists $name";
    }
    protected function wrapValue(string $value): string
    {
        if ($value !== '*') {
            return '`' . str_replace('`', '``', $value) . '`';
        }

        return $value;
    }
    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        $sql = sprintf(
            'alter table %s add constraint %s ',
            $this->wrapTable($blueprint->getTable()),
            $this->wrap($command->index)
        );

        $sql .= sprintf(
            'foreign key (%s) references %s (%s)',
            $this->wrap($command->column),
            $this->wrapTable($command->on),
            $this->parseColumns((array) $command->references)
        );

        if (! is_null($command->onDelete)) {
            $sql .= " on delete {$command->onDelete}";
        }

        if (! is_null($command->onUpdate)) {
            $sql .= " on update {$command->onUpdate}";
        }

        return $sql;
    }
}
