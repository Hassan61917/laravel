<?php

namespace Src\Main\Console;

use Src\Main\Console\Traits\CallsCommands;
use Src\Main\Foundation\IApplication;
use Src\Symfony\Console\Commands\Command;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class AppCommand extends Command
{
    use CallsCommands;
    protected string $signature;
    protected IConsoleInput $input;
    protected IConsoleOutput $output;
    protected IApplication $laravel;
    public function __construct(?string $name = null)
    {
        parent::__construct($this->formatName($name));
    }
    public function setLaravel(IApplication $laravel): static
    {
        $this->laravel = $laravel;

        return $this;
    }
    public function getLaravel(): IApplication
    {
        return $this->laravel;
    }
    public function getOutput(): IConsoleOutput
    {
        return $this->output;
    }
    public function getInputArgument(string $name): ?string
    {
        return $this->input->getArgument($name);
    }
    public function hasOption(string $name): bool
    {
        return $this->input->hasOption($name);
    }
    public function optionExists(string $name): bool
    {
        return $this->input->optionExists($name);
    }
    public function getOption(string $name): ?string
    {
        return $this->input->getOption($name);
    }
    public function run(IConsoleInput $input, IConsoleOutput $output): int
    {
        $this->input = $input;
        $this->output = $output;
        return parent::run($input, $output);
    }
    protected function execute(IConsoleInput $input, IConsoleOutput $output): int
    {
        return (int) $this->laravel->call([$this, 'handle']);
    }
    protected function configure(): void
    {
        [$arguments, $options] = SignatureParser::getParameters($this->signature ?? "");

        $this->setParameters($arguments, $options);
    }
    protected function setParameters(array $arguments = [], array $options = []): void
    {
        $arguments = array_merge($arguments, $this->getArguments());

        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }

        $options = array_merge($options, $this->getOptions());

        foreach ($options as $option) {
            $this->addOption($option);
        }
    }
    protected function getArguments(): array
    {
        return [];
    }
    protected function getOptions(): array
    {
        return [];
    }
    protected function resolveCommand(string $command): Command
    {
        if (! class_exists($command)) {
            return $this->getApplication()->findCommand($command);
        }

        $command = $this->laravel->make($command);

        if ($command instanceof Command) {
            $command->setApplication($this->getApplication());
        }

        if ($command instanceof AppCommand) {
            $command->setLaravel($this->laravel);
        }

        return $command;
    }
    protected function formatName(?string $name = null): string
    {
        if ($name) {
            return $name;
        }

        if (isset($this->signature)) {
            return SignatureParser::getName($this->signature);
        }

        return CommandHelper::createName(class_basename($this));
    }
    protected function write(string $text): void
    {
        $this->output->write($text);
    }
}
