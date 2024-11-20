<?php

namespace Src\Main\Database\Schema\Blueprint;

use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Schema\Blueprint\Traits\WithColumns;
use Src\Main\Database\Schema\Blueprint\Traits\WithCommands;
use Src\Main\Database\Schema\Fluent;
use Src\Main\Database\Schema\Grammars\SchemaGrammar;

class Blueprint
{
    use WithColumns,
        WithCommands;

    public string $collation;
    public function __construct(
        protected string $table,
        protected string $prefix
    ) {}
    public function setCollation(string $collation): static
    {
        $this->collation = $collation;

        return $this;
    }
    public function getCollation(): string
    {
        return $this->collation;
    }
    public function getTable(): string
    {
        return $this->table;
    }
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    public function creating(): bool
    {
        return collect($this->commands)->contains(fn($command) => $command->name === 'create');
    }
    public function getAddedColumns(): array
    {
        return array_filter($this->columns, fn($column) => ! $column->change);
    }
    public function getChangedColumns(): array
    {
        return array_filter($this->columns, fn($column) => (bool) $column->change);
    }
    public function build(Connection $connection, SchemaGrammar $grammar): void
    {
        $this->bootCommands($connection, $grammar);

        foreach ($this->toSql($connection, $grammar) as $statement) {
            $connection->statement($statement);
        }
    }
    public function toSql(Connection $connection, SchemaGrammar $grammar): array
    {
        $statements = [];

        foreach ($this->commands as $command) {
            if (!$command->skip) {
                $statement = $this->compileCommand($command, $grammar, $connection);
                if ($statement) {
                    $statements[] = $statement;
                }
            }
        }

        return $statements;
    }
    public function hasCommand(string $name): bool
    {
        return $this->getCommandByName($name) != null;
    }
    public function getCommandByName(string $name): ?Fluent
    {
        return $this->getCommandsByNames($name)[0] ?? null;
    }
    protected function bootCommands(Connection $connection, SchemaGrammar $grammar): void
    {
        if (!$this->creating()) {
            if (count($this->getAddedColumns())) {
                array_unshift($this->commands, $this->createCommand('add'));
            }

            if (count($this->getChangedColumns())) {
                array_unshift($this->commands, $this->createCommand('change'));
            }
        }

        $this->addIndexes($connection, $grammar);

        $this->addGrammarCommands($connection, $grammar);
    }
    protected function addIndexes(Connection $connection, SchemaGrammar $grammar): void
    {
        $indexes = ['primary', 'unique', 'index'];

        foreach ($this->columns as $column) {
            foreach ($indexes as $index) {
                if ($column->exists($index)) {
                    $this->addIndex($index, $column);
                }
            }
        }
    }
    protected function addGrammarCommands(Connection $connection, SchemaGrammar $grammar): void
    {
        foreach ($this->columns as $column) {
            foreach ($grammar->getCommands() as $command) {
                $this->addCommand($command, compact('column'));
            }
        }
    }
    protected function addIndex(string $index, ColumnDefinition $column): void
    {
        $status = (bool) $column->{$index};

        if ($status) {
            $this->{$index}($column->name);
            $column->rest($index);
        }

        if (!$status && $column->change) {
            $this->{'drop' . ucfirst($index)}([$column->name]);
            $column->rest($index);
        }
    }
    protected function compileCommand(Fluent $command, SchemaGrammar $grammar, Connection $connection): ?string
    {
        $method = 'compile' . ucfirst($command->name);

        if (method_exists($grammar, $method)) {

            $sql = $grammar->$method($this, $command, $connection);

            if ($sql) {
                return $sql;
            }
        }

        return null;
    }
    protected function getCommandsByNames(string ...$names): array
    {
        return array_filter($this->commands, fn($command) => in_array($command->name, $names));
    }
}
