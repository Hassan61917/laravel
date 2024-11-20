<?php

namespace Src\Main\Database;

use Src\Main\Database\Migrations\IMigrationRepository;
use Src\Main\Database\Migrations\MigrationCreator;
use Src\Main\Database\Migrations\MigrationRepository;
use Src\Main\Database\Migrations\Migrator;
use Src\Main\Filesystem\Filesystem;
use Src\Main\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "migration.creator" => [MigrationCreator::class],
            "migration.repository" => [IMigrationRepository::class, MigrationRepository::class],
            "migrator" => [Migrator::class],
        ];
    }
    public function register(): void
    {
        $this->registerCreator();
        $this->registerRepository();
        $this->registerMigrator();
    }
    protected function registerRepository(): void
    {
        $this->app->singleton('migration.repository', function ($app) {
            $migrations = $app['config']['database.migrations'];

            $table = $migrations['table'] ?? null;

            return new MigrationRepository($app['db'], $table);
        });
    }
    protected function registerMigrator(): void
    {
        $this->app->singleton('migrator', function ($app) {
            $repository = $app['migration.repository'];

            return new Migrator($repository, $app['db'], new Filesystem());
        });
    }
    protected function registerCreator(): void
    {
        $this->app->singleton('migration.creator', function ($app) {
            return new MigrationCreator(new Filesystem());
        });
    }
}
