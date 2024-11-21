<?php

namespace Src\Main\Foundation\Console\Commands\Events;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeEvent extends AbstractMakeGenerator
{
    protected string $path = "Events";
    protected string $type = "Event";
    protected string $stubsPath = "Events";
    protected string $description = 'Create a new Event class';
    protected function getDefaultStub(): string
    {
        return "event.stub";
    }
}
