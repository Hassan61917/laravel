<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeFactory extends AbstractMakeGenerator
{
    protected string $path = "Factories";
    protected string $type = "Factory";
    protected string $stubsPath = "Database";
    protected string $description = 'Create a new factory class';
    protected function rootNamespace(): string
    {
        return "Database";
    }
    protected function getDefaultStub(): string
    {
        return "factory.stub";
    }
}
