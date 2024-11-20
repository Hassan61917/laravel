<?php

namespace Src\Main\Database\Migrations;

use Closure;
use InvalidArgumentException;
use Src\Main\Filesystem\Filesystem;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;
use Src\Main\Utils\Str;

class MigrationCreator
{
    protected string $stubPath;
    protected string $defaultStub;
    protected IObserverList $afterCreate;
    public function __construct(
        protected Filesystem $files
    ) {
        $this->afterCreate = new ObserverList();
    }
    public function setStubPath(string $path): static
    {
        $this->stubPath = $path;

        return $this;
    }
    public function setDefaultStub(string $path): static
    {
        $this->defaultStub = $path;

        return $this;
    }
    public function afterCreate(Closure $callback): static
    {
        $this->afterCreate->add($callback);

        return $this;
    }
    public function create(string $name, string $path, ?string $table = null, bool $create = false): string
    {
        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $this->files->put(
            $path,
            $this->populateStub($stub, $table)
        );

        $this->fireAfterCreate($table, $path);

        return $path;
    }
    protected function ensureMigrationDoesntAlreadyExist(string $name, ?string $migrationPath = null): void
    {
        if ($migrationPath) {
            $migrationFiles = $this->files->glob($migrationPath . '/*.php');

            foreach ($migrationFiles as $migrationFile) {
                $this->files->requireOnce($migrationFile);
            }
        }

        $className = $this->getClassName($name);

        if (class_exists($className)) {
            throw new InvalidArgumentException("A {$className} class already exists.");
        }
    }
    protected function getClassName(string $name): string
    {
        return Str::studly($name);
    }
    protected function getStub(?string $table, bool $create): ?string
    {
        $path = $this->defaultStub ?? $this->stubPath . $this->getType($table, $create);

        return $this->files->get($path);
    }
    protected function getType(?string $table, bool $create): string
    {
        if (is_null($table)) {
            return "/migration.stub";
        }

        if ($create) {
            return "/migration.create.stub";
        }

        return "/migration.update.stub";
    }
    protected function getPath(string $name, string $path): string
    {
        $date = $this->getDatePrefix();

        return "{$path}/{$date}_{$name}.php";
    }
    protected function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }
    protected function populateStub(string $stub, ?string $table): string
    {
        if ($table) {
            $stub = str_replace('{{table}}', $table, $stub);
        }

        return $stub;
    }
    protected function fireAfterCreate(?string $table, string $path): void
    {
        $this->afterCreate->run([$table, $path]);
    }
}
