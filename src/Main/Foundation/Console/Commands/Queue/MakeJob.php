<?php

namespace Src\Main\Foundation\Console\Commands\Queue;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeJob extends AbstractMakeGenerator
{
    protected string $path = "Jobs";
    protected string $type = "Job";
    protected string $stubsPath = "Queue";
    protected string $description = 'Create a new job class';
    protected function getDefaultStub(): string
    {
        return "job.stub";
    }
}
