<?php

namespace Src\Main\Foundation\Console\Commands\Events;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class MakeListener extends AbstractMakeGenerator
{
    protected string $path = "Listeners";
    protected string $type = "Listener";
    protected string $stubsPath = "Events";
    protected string $description = 'Create a new Listener class';
    protected function getDefaultStub(): string
    {
        return "listener.stub";
    }
    protected function getCommandOptions(): array
    {
        return [
            new InputOption("event", "e", 'The event class being listened for', InputMode::Optional),
        ];
    }
    protected function buildClass(string $name): string
    {
        $event = $this->getOption('event') ?? '';

        if ($event) {
            $event = $this->laravel->getNamespace() . '\\Events\\' . str_replace('/', '\\', $event);
        }

        $stub = str_replace(
            ['{{event}}'],
            class_basename($event),
            parent::buildClass($name)
        );

        return str_replace(
            ['{{eventNamespace}}'],
            trim($event, '\\'),
            $stub
        );
    }
}
