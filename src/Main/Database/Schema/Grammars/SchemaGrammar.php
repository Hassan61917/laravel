<?php

namespace Src\Main\Database\Schema\Grammars;

use Src\Main\Database\BaseGrammar;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Database\Schema\Blueprint\ColumnDefinition;
use Src\Main\Database\Schema\Fluent;

abstract class SchemaGrammar extends BaseGrammar
{
    protected array $modifiers = [];
    protected array $commands = [];
    protected array $types = [];
    public function getCommands(): array
    {
        return $this->commands;
    }
    public function getModifiers(): array
    {
        return $this->modifiers;
    }
    public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection): ?string
    {
        $table =  $this->wrapTable($blueprint->getTable());
        $from = $this->wrap($command->from);
        $to = $this->wrap($command->to);

        return "alter table {$table} rename column {$from} to {$to}";
    }
    public function prefixArray(string $prefix, array $values): array
    {
        return array_map(function ($value) use ($prefix) {
            return $prefix . ' ' . $value;
        }, $values);
    }
    protected function getDefaultValue(bool|string $value): string
    {
        $value = is_bool($value) ? (int) $value : $value;

        return "'{$value}'";
    }
    protected function parseBlueprintColumns(Blueprint $blueprint): array
    {
        $columns = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            $sql = $this->wrap($column->name) . ' ' . $this->getType($column);

            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }

        return $columns;
    }
    protected function getType(ColumnDefinition $column): string
    {
        $type = $column->type;

        if (array_key_exists($type, $this->types)) {
            return $this->types[$type];
        }

        $type = ucfirst($column->type);

        $method = "type{$type}";

        if (method_exists($this, $method)) {
            return $this->$method($column);
        }

        return "";
    }
    protected function addModifiers(string $sql, Blueprint $blueprint, Fluent $column): string
    {
        foreach ($this->modifiers as $modifier) {
            $method = "modify{$modifier}";

            if (method_exists($this, $method)) {
                $sql .= $this->{$method}($blueprint, $column);
            }
        }
        return $sql;
    }
    public abstract function compileCreateDatabase(string $name, Connection $connection): string;
    public abstract function compileDropDatabase(string $name): string;
    public abstract function compileTables(string $database): string;
    public abstract function compileViews(string $database): string;
    public abstract function compileColumns(string $database, string $table): string;
    public abstract function compileIndexes(string $database, string $table): string;
    public abstract function compileForeignKeys(string $database, string $table): string;
    public abstract function compileDropAllTables(array $tables): string;
    public abstract function compileDropAllViews(array $views): string;
    public abstract function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection): string;
    public abstract function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection): string;
    public abstract function compileForeign(Blueprint $blueprint, Fluent $command): string;
    public abstract function compileDropForeign(Blueprint $blueprint, Fluent $command): string;
}
