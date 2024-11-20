<?php

namespace Src\Main\Database\Migrations;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use ReflectionClass;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\IConnectionResolver;
use Src\Main\Filesystem\Filesystem;
use Src\Main\Utils\Str;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class Migrator
{
    protected string $connection;
    protected array $paths = [];
    protected IConsoleOutput $output;
    protected static array $requiredPathCache = [];
    public function __construct(
        protected IMigrationRepository $repository,
        protected IConnectionResolver $resolver,
        protected Filesystem $files,
    ) {}
    public function setConnection(string $name): void
    {
        $this->resolver->setDefaultConnection($name);

        $this->repository->setConnection($name);

        $this->connection = $name;
    }
    public function setOutput(IConsoleOutput $output): static
    {
        $this->output = $output;
        return $this;
    }
    public function usingConnection(?string $name, Closure $callback)
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        $this->setConnection($name);

        $result = $callback();

        $this->setConnection($previousConnection);

        return $result;
    }
    public function getRepository(): IMigrationRepository
    {
        return $this->repository;
    }
    public function path(string $path): void
    {
        $this->paths[] = $path;
    }
    public function paths(): array
    {
        return $this->paths;
    }
    public function repositoryExists(): bool
    {
        return $this->repository->repositoryExists();
    }
    public function hasRunAnyMigrations(): bool
    {
        return $this->repositoryExists() && count($this->repository->getRan()) > 0;
    }
    public function resolveConnection(string $connection): Connection
    {
        return $this->resolver->connection($connection);
    }
    public function deleteRepository(): void
    {
        $this->repository->deleteRepository();
    }
    public function run(array $paths = [], array $options = []): array
    {
        $files = $this->getMigrationFiles($paths);

        $migrations = $this->pendingMigrations(
            $files,
            $this->repository->getRan()
        );

        $this->requireFiles($migrations);

        $this->runPending($migrations, $options);

        return $migrations;
    }
    public function rollback(array $paths = [], array $options = []): array
    {
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {

            $this->write('Nothing to rollback.');

            return [];
        }

        return $this->rollbackMigrations($migrations, $paths, $options);
    }
    public function reset(array $paths = []): array
    {
        $migrations = array_reverse($this->repository->getRan());

        if (count($migrations) === 0) {
            $this->write('Nothing to rollback.');

            return [];
        }

        return $this->resetMigrations($migrations, $paths);
    }
    public function getMigrationName(string $path): string
    {
        return str_replace('.php', '', basename($path));
    }
    public function getMigrationFiles(array $paths): array
    {
        $result = [];

        foreach (Arr::flatten($paths) as $path) {
            foreach ($this->files->glob($path . '/*_*.php') as $file) {
                $result[$this->getMigrationName($file)] = $file;
            }
        }

        return $result;
    }
    protected function pendingMigrations(array $files, array $ran)
    {
        return Collection::make($files)->reject(
            fn($file) =>
            in_array($this->getMigrationName($file), $ran)
        )->values()->all();
    }
    protected function requireFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }
    protected function runPending(array $migrations, array $options = []): void
    {
        if (count($migrations) === 0) {
            $this->write('Nothing to migrate');

            return;
        }

        $batch = $this->repository->getNextBatchNumber();

        $step = $options['step'] ?? false;

        $this->write('Running migrations.');

        foreach ($migrations as $file) {
            $this->runUp($file, $batch);

            if ($step) {
                $batch++;
            }
        }
    }
    protected function write(string $message): void
    {
        $this->output->write($message . "\n");
    }
    protected function runUp(string $file, int $batch): void
    {
        $migration = $this->resolveMigration($file);

        $name = $this->getMigrationName($file);

        try {
            $this->runMigration($migration, 'up');
            $this->write($name . "\t Done");
        } catch (\Exception $e) {
            $this->write($name . "\t Failed");
            throw $e;
        }

        $this->repository->log($name, $batch);
    }
    protected function resolveMigration(string $path): Migration
    {
        $class = $this->getMigrationClass($this->getMigrationName($path));

        if (class_exists($class) && realpath($path) == (new ReflectionClass($class))->getFileName()) {
            return new $class;
        }

        $migration = static::$requiredPathCache[$path] ??= $this->files->getRequire($path);

        if (is_object($migration)) {
            return method_exists($migration, '__construct')
                ? $this->files->getRequire($path)
                : clone $migration;
        }

        return new $class;
    }
    protected function getMigrationClass(string $migrationName): string
    {
        return Str::studly(
            implode('_', array_slice(explode('_', $migrationName), 4))
        );
    }
    protected function runMigration(Migration $migration, string $method): void
    {
        $connection = $this->resolveConnection(
            $migration->getConnection() ?: $this->connection
        );

        if (method_exists($migration, $method)) {
            $this->runMethod($connection, $migration, $method);
        }
    }
    protected function runMethod(Connection $connection, Migration $migration, string $method): void
    {
        $previousConnection = $this->resolver->getDefaultConnection();

        try {
            $this->resolver->setDefaultConnection($connection->getName());

            if ($method == "up") {
                $migration->up();
            } else {
                $migration->down();
            }
        } finally {
            $this->resolver->setDefaultConnection($previousConnection);
        }
    }
    protected function getMigrationsForRollback(array $options): array
    {
        if (($steps = $options['step'] ?? 0) > 0) {
            return $this->repository->getMigrations($steps);
        }

        if (($batch = $options['batch'] ?? 0) > 0) {
            return $this->repository->getMigrationsByBatch($batch);
        }

        return $this->repository->getLast();
    }
    protected function rollbackMigrations(array $migrations, array $paths, array $options = []): array
    {
        $rolledBack = [];

        $this->requireFiles($files = $this->getMigrationFiles($paths));

        $this->write('Rolling back migrations.');

        foreach ($migrations as $migration) {
            $file = Arr::get($files, $migration->migration);

            if (!$file) {
                $this->write("Migrate not found");

                continue;
            }

            $rolledBack[] = $file;

            $this->runDown($file, $migration);
        }

        return $rolledBack;
    }
    protected function runDown(string $file, object $migration): void
    {
        $instance = $this->resolveMigration($file);

        $this->getMigrationName($file);

        $this->runMigration($instance, 'down');

        $this->repository->delete($migration);
    }
    protected function resetMigrations(array $migrations, array $paths): array
    {

        $migrations = array_map(fn($m) => (object) ['migration' => $m], $migrations);

        return $this->rollbackMigrations(
            $migrations,
            $paths
        );
    }
}
