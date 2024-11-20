<?php

namespace Src\Main\Foundation\Console\Commands\Database;

class MigrateInstall extends AbstractMigration
{
    protected string $description = 'Create the migration repository';
    public function handle(): void
    {
        $repository = $this->migrator->getRepository();

        $repository->setConnection($this->getDatabase());

        if ($repository->repositoryExists()) {
            $this->output->write('Migrate table already created');
            return;
        }

        $repository->createRepository();

        $this->output->write('Migrate table created successfully.');
    }
}
