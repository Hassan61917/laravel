<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Database\Connections\Connection;

class Migrate extends AbstractMigration
{
    protected string $signature = 'migrate 
                {--seed : Indicates if the seed task should be re-run}
                {--seeder= : The class name of the root seeder}';

    protected string $description = 'Run the database migrations';

    public function handle(): int
    {
        $this->runMigrations();

        return 0;
    }
    protected function runMigrations(): void
    {
        $this->migrator->usingConnection($this->getDatabase(), function () {
            $this->prepareDatabase();

            $this->migrator->setOutput($this->output)
                ->run($this->getMigrationPaths());

            if ($this->getOption('seed')) {
                $this->call('db:seed', [
                    '--class' => $this->getOption('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
                    '--force' => true,
                ]);
            }
        });
    }
    protected function prepareDatabase(): void
    {
        if (! $this->repositoryExists()) {
            $this->createRepository();
        }

        if (! $this->migrator->hasRunAnyMigrations()) {
            $this->loadSchemaState();
        }
    }
    protected function repositoryExists(): bool
    {
        try {
            return $this->migrator->repositoryExists();
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
            return false;
        }
    }
    protected function createRepository(): void
    {
        $this->callSilent('migrate:install', array_filter([
            '--database' => $this->getDatabase(),
        ]));

        $this->output->write('Migrate table created successfully.');
    }
    protected function loadSchemaState(): void
    {
        $connection = $this->migrator->resolveConnection($this->getDatabase());

        $path = $this->schemaPath($connection);

        if (! is_file($path)) {
            return;
        }

        $this->output->write('Loading stored database schemas.');

        $this->migrator->deleteRepository();
    }
    protected function schemaPath(Connection $connection): string
    {
        $connectionName = $connection->getName();

        $path = base_path("database/schema/{$connectionName}-schema.dump");

        if (file_exists($path)) {
            return $path;
        }

        return base_path("database/schema/{$connectionName}-schema.sql");
    }
}
