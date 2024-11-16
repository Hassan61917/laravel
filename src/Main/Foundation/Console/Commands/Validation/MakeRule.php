<?php

namespace Src\Main\Foundation\Console\Commands\Validation;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeRule extends AbstractMakeGenerator
{
    protected string $path = "Rules";
    protected string $type = "Rule";
    protected string $stubsPath = "Validation";
    protected string $description = 'Create a new Validation Rule class';
    protected function getDefaultStub(): string
    {
        return "rule.stub";
    }
}
