<?php

namespace Src\Main\Foundation\Console\Commands;

use Src\Main\Console\AppCommand;
use Src\Main\Filesystem\Filesystem;
abstract class AbstractMigrationGenerator extends AppCommand
{
    protected string $stubPath = "";
    protected Filesystem $files;
    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
    }
    public function handle(): int
    {
        $table = $this->migrationTableName();

        if ($this->migrationExists($table)) {
            $this->output->write('Migrate already exists.');

            return 1;
        }

        $this->createMigration($table);

        $this->output->write('Migrate created successfully.');

        return 0;
    }
    protected abstract function migrationTableName(): string;
    protected abstract function migrationStubFile(): string;
    protected function migrationExists(string $table): bool
    {
        $filePath = '*_*_*_*_create_' . $table . '_table.php';

        $path = join_paths($this->getMigrationPath(), $filePath);

        $files = $this->files->glob($path);

        return count($files) > 0;
    }
    protected function createMigration(string $table, bool $create = false): string
    {
        $name = "create_{$table}_table";

        return $this->laravel['migration.creator']
            ->setStubPath($this->getMigrationStubPath())
            ->setDefaultStub($this->getStub())
            ->create($name, $this->getMigrationPath(), $table, $create);
    }
    protected function getMigrationStubPath(): string
    {
        return $this->joinStubPaths("Database");
    }
    protected function getStub(): string
    {
        return $this->joinStubPaths($this->stubPath, $this->migrationStubFile());
    }
    protected function getMigrationPath(): string
    {
        return $this->laravel->databasePath('migrations');
    }
    protected function joinStubPaths(string ...$paths): string
    {
        return join_paths(dirname(__DIR__), "Stubs", ...$paths);
    }
}
