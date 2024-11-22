<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeSeeder extends AbstractMakeGenerator
{
    protected string $path = "Seeders";
    protected string $type = "Seeder";
    protected string $stubsPath = "Database";
    protected string $description = 'Create a new seeder class';
    protected function rootNamespace(): string
    {
        return "Database";
    }
    protected function getDefaultStub(): string
    {
        return "seeder.stub";
    }
}
