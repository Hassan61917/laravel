<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Console\AppCommand;
use Src\Main\Database\Migrations\MigrationCreator;
use Src\Main\Support\TableGuesser;
use Src\Main\Utils\Str;

class MakeMigration extends AppCommand
{
    protected string $signature = 'make:migration 
        {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}';

    protected string $description = 'Create a new migration file';
    public function __construct(
        protected MigrationCreator $creator
    ) {
        parent::__construct();
    }
    public function handle(): void
    {
        $name = Str::snake(trim($this->input->getArgument('name')));

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create') ?: false;

        if (! $table && is_string($create)) {
            $table = $create;

            $create = true;
        }

        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        $this->writeMigration($name, $table, $create);
    }
    protected function writeMigration(string $name, string $table, bool $create): void
    {
        $file = $this->creator
            ->setStubPath($this->getStubPath())
            ->create($name, $this->getMigrationPath(), $table, $create);

        $this->output->write("Migration [$file] created successfully.");
    }
    protected function getMigrationPath(): string
    {
        return base_path('database/migrations');
    }
    protected function getStubPath(): string
    {
        return join_paths(dirname(__DIR__, 2), "Stubs", "Database");
    }
}
