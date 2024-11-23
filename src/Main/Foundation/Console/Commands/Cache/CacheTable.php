<?php

namespace Src\Main\Foundation\Console\Commands\Cache;

use Src\Main\Foundation\Console\Commands\AbstractMigrationGenerator;

class CacheTable extends AbstractMigrationGenerator
{
    protected string $stubPath = "Cache";
    protected string $description = 'Create a migration for the cache database table';
    public function __construct()
    {
        parent::__construct("cache:table");
    }
    protected function migrationTableName(): string
    {
        return 'cache';
    }
    protected function migrationStubFile(): string
    {
        return 'cache.stub';
    }
}
