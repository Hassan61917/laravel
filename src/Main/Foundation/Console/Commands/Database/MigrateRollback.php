<?php

namespace Src\Main\Foundation\Console\Commands\Database;

class MigrateRollback extends AbstractMigration
{
    protected string $description = 'Rollback the last database migration';
    public function handle(): int
    {
        $this->migrator->usingConnection($this->getDatabase(), function () {
            $this->migrator->setOutput($this->output)->rollback($this->getMigrationPaths());
        });

        return 0;
    }
}
