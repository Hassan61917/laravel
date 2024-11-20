<?php

namespace Src\Main\Foundation\Console\Commands\Database;

class MigrateRefresh extends MigrateFresh
{
    protected string $description = 'Reset and re-run all migrations';
    public function handle(): int
    {
        $database = $this->getDatabase();

        $this->runReset($database);

        $this->call('migrate', array_filter([
            '--database' => $database,
            '--force' => true,
        ]));

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }

        return 0;
    }
    protected function runReset(string $database): void
    {
        $this->call('migrate:reset', array_filter([
            '--database' => $database,
            '--force' => true,
        ]));
    }
}
