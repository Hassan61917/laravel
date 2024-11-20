<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Database\Migrations\Migrator;

abstract class AbstractMigration extends AbstractDbCommand
{
    public function __construct(
        protected Migrator $migrator
    ) {
        parent::__construct();
    }
    protected function getMigrationPaths(): array
    {
        return array_merge(
            $this->migrator->paths(),
            [$this->getMigrationPath()]
        );
    }
    protected function getMigrationPath(): string
    {
        return base_path('database/migrations');
    }
}
