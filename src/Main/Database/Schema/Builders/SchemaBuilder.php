<?php

namespace Src\Main\Database\Schema\Builders;

use Closure;
use Illuminate\Support\Arr;
use LogicException;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Database\Schema\Grammars\SchemaGrammar;

abstract class SchemaBuilder
{
    protected static int $defaultStringLength = 255;
    protected static string $defaultMorphKeyType = 'int';
    protected SchemaGrammar $grammar;
    public function __construct(
        protected Connection $connection,
    ) {
        $this->grammar = $this->connection->getSchemaGrammar();
    }
    public static function setDefaultStringLength(int $defaultStringLength): void
    {
        self::$defaultStringLength = $defaultStringLength;
    }
    public static function getDefaultStringLength(): int
    {
        return self::$defaultStringLength;
    }
    public static function getDefaultMorphKeyType(): string
    {
        return self::$defaultMorphKeyType;
    }
    public function getConnection(): Connection
    {
        return $this->connection;
    }
    public function getGrammar(): SchemaGrammar
    {
        return $this->grammar;
    }
    public function createDatabase(string $name): bool
    {
        return $this->connection->statement(
            $this->grammar->compileCreateDatabase($name, $this->connection)
        );
    }
    public function dropDatabase(string $name): bool
    {
        return $this->connection->statement(
            $this->grammar->compileDropDatabase($name)
        );
    }
    public function getTables(): array
    {
        $sql = $this->grammar->compileTables($this->getDatabaseName());

        return $this->connection->getQueryProcessor()->processTables($this->select($sql));
    }
    public function getTableListing(): array
    {
        return array_column($this->getTables(), 'name');
    }
    public function hasTable(string $table): bool
    {
        $table = strtolower($this->getTableName($table));

        return Arr::first($this->getTables(), fn($t) => $table === strtolower($t["name"])) != null;
    }
    public function getViews(): array
    {
        $sql = $this->grammar->compileViews($this->getDatabaseName());

        return $this->connection->getQueryProcessor()->processViews($this->select($sql));
    }
    public function hasView(string $view): bool
    {
        $view = strtolower($this->getTableName($view));

        return Arr::first($this->getViews(), fn($v) => $view === strtolower($v["name"])) != null;
    }
    public function getColumns(string $table): array
    {
        $table = $this->getTableName($table);

        $sql = $this->grammar->compileColumns($this->getDatabaseName(), $table);

        return $this->connection->getQueryProcessor()->processColumns($this->select($sql));
    }
    public function getColumnListing(string $table): array
    {
        return array_column($this->getColumns($table), 'name');
    }
    public function hasColumn(string $table, string $column): bool
    {
        return in_array(
            strtolower($column),
            array_map(fn($c) => strtolower($c), $this->getColumnListing($table))
        );
    }
    public function getIndexes(string $table): array
    {
        $table = $this->getTableName($table);

        $sql = $this->grammar->compileIndexes($this->getDatabaseName(), $table);

        return $this->connection->getQueryProcessor()->processIndexes($this->select($sql));
    }
    public function getIndexListing(string $table): array
    {
        return array_column($this->getIndexes($table), 'name');
    }
    public function hasIndex(string $table, string $index, ?string $type = null): bool
    {
        if ($type) {
            $type = strtolower($type);
        }

        foreach ($this->getIndexes($table) as $value) {
            $typeMatches = is_null($type)
                || ($type === 'primary' && $value['primary'])
                || ($type === 'unique' && $value['unique'])
                || $type === $value['type'];

            if (($value['name'] === $index || in_array($index, $value['columns'])) && $typeMatches) {
                return true;
            }
        }

        return false;
    }
    public function getForeignKeys(string $table): array
    {
        $table = $this->getTableName($table);

        $sql = $this->grammar->compileForeignKeys($this->getDatabaseName(), $table);

        return $this->connection->getQueryProcessor()->processForeignKeys($this->select($sql));
    }
    public function create(string $table, Closure $callback): static
    {
        $blueprint = $this->createBlueprint($table);

        $blueprint->create();

        $callback($blueprint);

        $this->buildBlueprint($blueprint);

        return $this;
    }
    public function rename(string $from, string $to): void
    {
        $blueprint = $this->createBlueprint($from);

        $blueprint->rename($to);

        $this->buildBlueprint($blueprint);
    }
    public function dropAllTables(): void
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }
    public function dropAllViews(): void
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }
    public function drop(string $table): void
    {
        $blueprint = $this->createBlueprint($table);

        $blueprint->drop();

        $this->buildBlueprint($blueprint);
    }
    public function dropIfExists(string $table): void
    {
        $blueprint = $this->createBlueprint($table);

        $blueprint->dropIfExists();

        $this->buildBlueprint($blueprint);
    }
    public function dropColumns(string $table, string ...$columns): void
    {
        $blueprint = $this->createBlueprint($table);

        $blueprint->dropColumns(...$columns);

        $this->buildBlueprint($blueprint);
    }
    protected function select(string $sql): array
    {
        return $this->connection->select($sql);
    }
    protected function getDatabaseName(): string
    {
        return $this->connection->getDatabaseName();
    }
    protected function getTableName(string $table): string
    {
        return $this->connection->getTablePrefix() . $table;
    }
    protected function createBlueprint(string $table): Blueprint
    {
        $prefix = $this->connection->getTablePrefix();

        return new Blueprint($table, $prefix);
    }
    protected function buildBlueprint(Blueprint $blueprint): void
    {
        $blueprint->build($this->connection, $this->grammar);
    }
}
