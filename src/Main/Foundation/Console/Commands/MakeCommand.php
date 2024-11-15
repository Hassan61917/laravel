<?php

namespace Src\Main\Foundation\Console\Commands;

use Src\Main\Utils\Str;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class MakeCommand extends AbstractMakeGenerator
{
    protected string $path = "Console/Commands";
    protected string $type = "Command";
    protected string $stubsPath = "";
    protected string $description = 'Create a new Artisan command';
    protected function buildClass(string $name): string
    {
        $stub = parent::buildClass($name);

        return $this->replaceCommand($stub);
    }
    protected function replaceCommand(string $stub): string
    {
        $command = $this->getOption('command') ?: 'app:' . Str::kebab(class_basename($this->getNameInput()));
        return str_replace("{{command}}", $command, $stub);
    }
    protected function getDefaultStub(): string
    {
        return "command.stub";
    }
    protected function getCommandOptions(): array
    {
        return [
            new InputOption(
                "command",
                null,
                'The terminal command that will be used to invoke the class',
                InputMode::Optional
            )
        ];
    }
}
