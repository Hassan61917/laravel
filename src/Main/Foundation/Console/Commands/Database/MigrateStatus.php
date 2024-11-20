<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Illuminate\Support\Collection;

class MigrateStatus extends AbstractMigration
{
    protected string $description = 'Show the status of each migration';
    public function handle(): int
    {
        return $this->migrator->usingConnection($this->getDatabase(), function () {

            if (! $this->migrator->repositoryExists()) {
                $this->output->write('Migration table not found.');

                return 1;
            }

            $ran = $this->migrator->getRepository()->getRan();

            $batches = $this->migrator->getRepository()->getMigrationBatches();

            $migrations = $this->getStatusFor($ran, $batches);

            if (count($migrations) > 0) {

                foreach ($migrations as $migration) {
                    $this->output->write("$migration[0]\t$migration[1] \n");
                }
            } else {
                $this->output->write('No migrations found');
            }

            return 0;
        });
    }
    protected function getStatusFor(array $ran, array $batches): Collection
    {
        return Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran, $batches) {
                $migrationName = $this->migrator->getMigrationName($migration);

                $status = in_array($migrationName, $ran) ? "Ran" : 'Pending';

                return [$migrationName, $status];
            });
    }
    protected function getAllMigrationFiles(): array
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPaths());
    }
}
