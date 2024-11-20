<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class MigrateFresh extends AbstractMigration
{
    protected string $description = 'Drop all tables and re-run all migrations';
    public function handle(): int
    {
        $database = $this->getDatabase();

        $arguments = ['--database' => $database, '--force' => true];

        if ($this->migrator->repositoryExists()) {
            $this->output->write("\n");

            $this->callSilent('db:wipe', $arguments);
        }

        $this->output->write("\n");

        $this->call('migrate', $arguments);

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }

        return 0;
    }
    protected function needsSeeding(): bool
    {
        return $this->getOption('seed') || $this->getOption('seeder');
    }
    protected function runSeeder(string $database): void
    {
        $this->call('db:seed', array_filter([
            '--database' => $database,
            '--class' => $this->getOption('seeder') ?: 'Database\\Seeders\\DatabaseSeeder',
            '--force' => true,
        ]));
    }
    protected function extraOptions(): array
    {
        return [
            new InputOption("seed", null, 'Indicates if the seed task should be re-run', InputMode::None),
            new InputOption("seeder", null, 'The class name of the root seeder', InputMode::Optional),
        ];
    }
}
