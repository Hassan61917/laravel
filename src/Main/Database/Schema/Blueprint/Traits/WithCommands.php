<?php

namespace Src\Main\Database\Schema\Blueprint\Traits;

use Src\Main\Database\Schema\Fluent;
use Src\Main\Database\Schema\ForeignKeyDefinition;

trait WithCommands
{
    protected array $commands = [];
    public function getCommands(): array
    {
        return $this->commands;
    }
    public function primary(string ...$columns): Fluent
    {
        return $this->addIndexCommand('primary', ...$columns);
    }
    public function unique(string ...$columns): Fluent
    {
        return $this->addIndexCommand('unique', ...$columns);
    }
    public function index(string ...$columns): Fluent
    {
        return $this->addIndexCommand('index', ...$columns);
    }
    public function foreign(string $column, string $name = null): ForeignKeyDefinition
    {
        $command = new ForeignKeyDefinition(
            $this->addIndexCommand('foreign', $column, $name)->getAttributes()
        );

        $this->commands[count($this->commands) - 1] = $command;

        return $command;
    }
    public function create(): Fluent
    {
        return $this->addCommand('create');
    }
    public function rename(string $to): Fluent
    {
        return $this->addCommand('rename', compact('to'));
    }
    public function renameIndex(string $from, string $to): Fluent
    {
        return $this->addCommand('renameIndex', compact('from', 'to'));
    }
    public function renameColumn(string $from, string $to): Fluent
    {
        return $this->addCommand('renameColumn', compact('from', 'to'));
    }
    public function drop(): Fluent
    {
        return $this->addCommand('drop');
    }
    public function dropIfExists(): Fluent
    {
        return $this->addCommand('dropIfExists');
    }
    public function dropColumns(string ...$columns): Fluent
    {
        return $this->addCommand('dropColumn', compact('columns'));
    }
    public function dropPrimary(string $column): Fluent
    {
        return $this->dropIndexCommand('dropPrimary', 'primary', $column);
    }
    public function dropUnique(string $column): Fluent
    {
        return $this->dropIndexCommand('dropUnique', 'unique', $column);
    }
    public function dropIndex(string $column): Fluent
    {
        return $this->dropIndexCommand('dropIndex', 'index', $column);
    }
    public function dropForeign(string $index): Fluent
    {
        return $this->dropIndexCommand('dropForeign', 'foreign', $index);
    }
    public function dropConstrainedForeignId(string $column): Fluent
    {
        $this->dropForeign($column);

        return $this->dropColumns($column);
    }
    public function dropTimestamps(): void
    {
        $this->dropColumns('created_at', 'updated_at');
    }
    public function dropSoftDeletes(string $column = 'deleted_at'): void
    {
        $this->dropColumns($column);
    }
    public function dropRememberToken(): void
    {
        $this->dropColumns('remember_token');
    }
    public function dropMorphs(string $name): void
    {
        $this->dropIndex($this->createIndexName('index', "{$name}_type"));
        $this->dropIndex($this->createIndexName('index',  "{$name}_id"));
        $this->dropColumns("{$name}_type", "{$name}_id");
    }
    protected function addCommand(string $name, array $parameters = []): Fluent
    {
        $command = $this->createCommand($name, $parameters);

        $this->commands[] = $command;

        return $command;
    }
    protected function createCommand($name, array $parameters = []): Fluent
    {
        return new Fluent(array_merge(compact('name'), $parameters));
    }
    protected function addIndexCommand(string $type, string $column, string $index = null): Fluent
    {
        $index = $index ?: $this->createIndexName($type, $column);

        return $this->addCommand(
            $type,
            compact('column', 'index')
        );
    }
    protected function createIndexName(string $type, string $column): string
    {
        $table = str_contains($this->table, '.')
            ? substr_replace($this->table, '.' . $this->prefix, strrpos($this->table, '.'), 1)
            : $this->prefix . $this->table;

        $index = strtolower("{$table}_{$column}_{$type}");

        return str_replace(".", '_', $index);
    }
    protected function dropIndexCommand(string $command, string $type, string $column): Fluent
    {
        $index = $this->createIndexName($type, $column);

        return $this->addIndexCommand($command, $column, $index);
    }
}
