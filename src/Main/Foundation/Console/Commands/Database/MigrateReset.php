<?php

namespace Src\Main\Foundation\Console\Commands\Database;

class MigrateReset extends AbstractMigration
{
    protected string $description = 'Rollback all database migrations';
    public function handle()
    {
        return $this->migrator->usingConnection($this->getDatabase(), function () {

            if (! $this->migrator->repositoryExists()) {
                $this->output->write('Migration table not found.');
                return;
            }

            $this->migrator
                ->setOutput($this->output)
                ->reset($this->getMigrationPaths());
        });
    }
}
