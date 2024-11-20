<?php

namespace Src\Main\Database\Schema\Grammars;

use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Schema\Blueprint\Blueprint;
use Src\Main\Database\Schema\Fluent;
use Src\Main\Database\Schema\Grammars\Traits\CompileModifiers;
use Src\Main\Database\Schema\Grammars\Traits\CompileTypes;

class MysqlSchemaGrammar extends SqlSchemaGrammar
{
    use CompileTypes,
        CompileModifiers;

    protected array $commands = ['AutoIncrementStartingValues'];
    protected array $modifiers = ['Unsigned', 'Collate', 'Nullable', 'Default', 'OnUpdate', 'Increment'];
    protected array $serials = ['bigInteger', 'integer'];
    public function compileTables(string $database): string
    {
        return sprintf(
            'select table_name as `name`, (data_length + index_length) as `size`, '
                . 'table_collation as `collation` '
                . "from information_schema.tables where table_schema = %s and table_type in ('BASE TABLE', 'SYSTEM VERSIONED') "
                . 'order by table_name',
            $this->quoteString($database)
        );
    }
    public function compileViews(string $database): string
    {
        return sprintf(
            'select table_name as `name`, view_definition as `definition` '
                . 'from information_schema.views where table_schema = %s '
                . 'order by table_name',
            $this->quoteString($database)
        );
    }
    public function compileColumns(string $database, string $table): string
    {
        return sprintf(
            'select column_name as `name`, data_type as `type_name`, column_type as `type`, '
                . 'collation_name as `collation`, is_nullable as `nullable`, '
                . 'column_default as `default`, extra as `extra` '
                . 'from information_schema.columns where table_schema = %s and table_name = %s '
                . 'order by ordinal_position asc',
            $this->quoteString($database),
            $this->quoteString($table)
        );
    }
    public function compileIndexes(string $database, string $table): string
    {
        return sprintf(
            'select index_name as `name`, group_concat(column_name order by seq_in_index) as `columns`, '
                . 'index_type as `type`, not non_unique as `unique` '
                . 'from information_schema.statistics where table_schema = %s and table_name = %s '
                . 'group by index_name, index_type, non_unique',
            $this->quoteString($database),
            $this->quoteString($table)
        );
    }
    public function compileForeignKeys(string $database, string $table): string
    {
        return sprintf(
            'select kc.constraint_name as `name`, '
                . 'group_concat(kc.column_name order by kc.ordinal_position) as `columns`, '
                . 'kc.referenced_table_schema as `foreign_schema`, '
                . 'kc.referenced_table_name as `foreign_table`, '
                . 'group_concat(kc.referenced_column_name order by kc.ordinal_position) as `foreign_columns`, '
                . 'rc.update_rule as `on_update`, '
                . 'rc.delete_rule as `on_delete` '
                . 'from information_schema.key_column_usage kc join information_schema.referential_constraints rc '
                . 'on kc.constraint_schema = rc.constraint_schema and kc.constraint_name = rc.constraint_name '
                . 'where kc.table_schema = %s and kc.table_name = %s and kc.referenced_table_name is not null '
                . 'group by kc.constraint_name, kc.referenced_table_schema, kc.referenced_table_name, rc.update_rule, rc.delete_rule',
            $this->quoteString($database),
            $this->quoteString($table)
        );
    }
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection): string
    {
        $result = $this->compileCreateTable($blueprint, $command, $connection);

        $result .= $this->compileCreateEncoding($connection, $blueprint);

        return $result;
    }
    public function compileDropAllTables(array $tables): string
    {
        return "drop table " . implode(',', $this->wrapArray($tables));
    }
    public function compileDropAllViews(array $views): string
    {
        return 'drop view ' . implode(',', $this->wrapArray($views));
    }
    public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection): string
    {
        $columns = [];

        foreach ($blueprint->getChangedColumns() as $column) {
            $sql = sprintf(
                '%s %s%s %s',
                is_null($column->renameTo) ? 'modify' : 'change',
                $this->wrap($column),
                is_null($column->renameTo) ? '' : ' ' . $this->wrap($column->renameTo),
                $this->getType($column)
            );

            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }

        return 'alter table ' . $this->wrapTable($blueprint->getTable()) . ' ' . implode(', ', $columns);
    }
    public function compileDrop(Blueprint $blueprint, Fluent $command): string
    {
        return 'drop table ' . $this->wrapTable($blueprint->getTable());
    }
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return 'drop table if exists ' . $this->wrapTable($blueprint->getTable());
    }
    public function compileDropColumn(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->prefixArray('drop', $this->wrapArray($command->columns));

        return 'alter table ' . $this->wrapTable($blueprint->getTable()) . ' ' . implode(', ', $columns);
    }
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command): string
    {
        return 'alter table ' . $this->wrapTable($blueprint->getTable()) . ' drop primary key';
    }
    public function compileDropUnique(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint->getTable())} drop index {$index}";
    }
    public function compileDropIndex(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint->getTable())} drop index {$index}";
    }
    public function compileDropForeign(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "alter table {$this->wrapTable($blueprint->getTable())} drop foreign key {$index}";
    }
    public function compileRename(Blueprint $blueprint, Fluent $command): string
    {
        $from = $this->wrapTable($blueprint->getTable());

        return "rename table {$from} to " . $this->wrapTable($command->to);
    }
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'alter table %s rename index %s to %s',
            $this->wrapTable($blueprint->getTable()),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }
    public function compileAdd(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->prefixArray('add', $this->parseBlueprintColumns($blueprint));

        return 'alter table ' . $this->wrapTable($blueprint->getTable()) . ' ' . implode(', ', $columns);
    }
    public function compileAutoIncrementStartingValues(Blueprint $blueprint, Fluent $command): string
    {
        if (
            $command->column->autoIncrement
            && $value = $command->column->get('startingValue', $command->column->get('from'))
        ) {
            return 'alter table ' . $this->wrapTable($blueprint->getTable()) . ' auto_increment = ' . $value;
        }
        return "";
    }
    public function compilePrimary(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'alter table %s add primary key (%s)',
            $this->wrapTable($blueprint->getTable()),
            $this->parseColumns([$command->column])
        );
    }
    public function compileUnique(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileKey($blueprint, $command, 'unique');
    }
    public function compileIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileKey($blueprint, $command, 'index');
    }
    protected function compileCreateTable(Blueprint $blueprint, Fluent $command, Connection $connection): string
    {
        $tableStructure = $this->parseBlueprintColumns($blueprint);

        $primaryKey = $blueprint->getCommandByName('primary');


        if ($primaryKey) {

            $tableStructure[] = sprintf(
                'primary key (%s)',
                $this->parseColumns($primaryKey->columns)
            );

            $primaryKey->shouldBeSkipped = true;
        }

        return sprintf(
            'create table %s (%s)',
            $this->wrapTable($blueprint->getTable()),
            implode(', ', $tableStructure)
        );
    }
    protected function compileCreateEncoding(Connection $connection, Blueprint $blueprint): string
    {
        if (isset($blueprint->collation)) {
            return " collate '{$blueprint->collation}'";
        } elseif (! is_null($collation = $connection->getOption('collation'))) {
            return " collate '{$collation}'";
        }

        return "";
    }
    protected function compileKey(Blueprint $blueprint, Fluent $command, $type): string
    {
        return sprintf(
            'alter table %s add %s %s(%s)',
            $this->wrapTable($blueprint->getTable()),
            $type,
            $this->wrap($command->index),
            $this->wrap($command->column)
        );
    }
}
