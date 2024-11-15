<?php

namespace Src\Main\Console\Traits;

use Src\Symfony\Console\Commands\Command;
use Src\Symfony\Console\Inputs\ArrayInput;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Outputs\IConsoleOutput;
use Src\Symfony\Console\Outputs\NullOutput;

trait CallsCommands
{
    abstract protected function resolveCommand(string $command): Command;

    public function call(string $command,  array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, $this->output);
    }
    public function callSilent(string $command, array $arguments = []): int
    {
        return $this->runCommand($command, $arguments, new NullOutput());
    }
    protected function runCommand(string $command, array $arguments, IConsoleOutput $output): int
    {
        $arguments['command'] = $command;

        return $this->resolveCommand($command)->run(
            $this->createInputFromArguments($arguments),
            $output
        );
    }
    protected function createInputFromArguments(array $arguments): IConsoleInput
    {
        $arguments = array_merge($this->context(), $arguments);
        return new ArrayInput($arguments);
    }
    protected function context(): array
    {
        return collect($this->getOptions())->only([
            'ansi',
            'no-ansi',
            'no-interaction',
            'quiet',
            'verbose',
        ])->filter()->mapWithKeys(function ($value, $key) {
            return ["--{$key}" => $value];
        })->all();
    }
}
