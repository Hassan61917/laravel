<?php

namespace Src\Main\Database\Schema\Builders;

class MysqlSchemaBuilder extends SchemaBuilder
{
    public function dropAllTables(): void
    {
        $tables = array_column($this->getTables(), 'name');

        if (empty($tables)) {
            return;
        }

        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );
    }
    public function dropAllViews(): void
    {
        $views = array_column($this->getViews(), 'name');

        if (empty($views)) {
            return;
        }

        $this->connection->statement(
            $this->grammar->compileDropAllViews($views)
        );
    }
}
