<?php

namespace Src\Main\Foundation\Console\Commands\Queue;

use Src\Main\Foundation\Console\Commands\AbstractMigrationGenerator;

class QueueTable extends AbstractMigrationGenerator
{
    protected string $stubPath = "Queue";
    protected string $description = 'Create a migration for the cache database table';
    protected function migrationTableName(): string
    {
        return $this->laravel['config']['queue.connections.database.table'];
    }
    protected function migrationStubFile(): string
    {
        return 'jobsTable.stub';
    }
}
