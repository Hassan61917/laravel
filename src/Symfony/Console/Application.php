<?php

namespace Src\Symfony\Console;

use Src\Symfony\Console\Commands\Command;
use Src\Symfony\Console\Commands\HelpCommand;
use Src\Symfony\Console\Commands\ListCommand;
use Src\Symfony\Console\Descriptors\Describable;
use Src\Symfony\Console\Descriptors\IDescriptor;
use Src\Symfony\Console\Exceptions\CommandNotFoundException;
use Src\Symfony\Console\Helpers\HelperSet;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputItem;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;
use Src\Symfony\Console\Loaders\ICommandLoader;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class Application implements Describable
{
    protected string $defaultCommand = "list";
    protected bool $help = false;
    protected bool $booted = false;
    protected array $commands = [];
    protected ?InputItem $inputItem = null;
    protected ICommandLoader $commandLoader;
    protected HelperSet $helperSet;
    protected IReplaceFinder $replaceFinder;
    public function __construct(
        protected string $name,
        protected string $version
    ) {
        $this->replaceFinder = new ReplaceFinder();
        $this->boot();
    }
    public function setCommandLoader(ICommandLoader $commandLoader): static
    {
        $this->commandLoader = $commandLoader;

        return $this;
    }
    public function setDefaultCommand(string $defaultCommand): static
    {
        $this->defaultCommand = $defaultCommand;

        return $this;
    }
    public function setHelperSet(HelperSet $helperSet): static
    {
        $this->helperSet = $helperSet;

        return $this;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getVersion(): string
    {
        $name = $this->name;

        $version = $this->version;

        return "{$version} <info>{$name}</info>";
    }
    public function getInputItem(): ?InputItem
    {
        return $this->inputItem ??= $this->createDefaultInputItem();
    }
    public function getHelperSet(): HelperSet
    {
        return $this->helperSet ??= $this->getDefaultHelperSet();
    }
    public function addCommand(Command $command): ?Command
    {
        $command->setApplication($this);

        if (!$command->isEnable()) {
            $command->setApplication(null);
            return null;
        }

        $this->pushCommand($command);

        return $command;
    }
    public function findCommand(string $name): ?Command
    {
        if (!$this->hasCommand($name)) {
            return null;
        }

        $command = $this->getCommand($name);

        return $command->isHidden() ? null : $command;
    }
    public function hasCommand(string $name): bool
    {
        $this->loadCommand($name);

        return isset($this->commands[$name]);
    }
    public function loadCommand(string $name): void
    {
        if (isset($this->commandLoader) && $this->commandLoader->has($name)) {
            $this->addCommand($this->commandLoader->get($name));
        }
    }
    public function getCommand(string $name): Command
    {
        if (!$this->hasCommand($name)) {
            throw new CommandNotFoundException("The command {$name} does not exist.");
        }

        $command = $this->commands[$name];

        if ($this->help) {
            $this->help = false;
            $helpCommand = $this->getHelpCommand();
            $helpCommand->setCommand($command);
            $command = $helpCommand;
        }

        return $command;
    }
    public function allCommands(?string $namespace = null): array
    {
        $this->boot();

        return $namespace
            ? $this->allCommandsFor($namespace)
            : $this->getCommands();
    }
    public function run(IConsoleInput $input, IConsoleOutput $output): int
    {
        $name = $this->getCommandName($input);

        if ($input->hasParameterOption("--help", "-h")) {
            $this->help = true;
        }

        $command = $this->findCommand($name);

        if (is_null($command)) {
            $commands = $this->findAlternatives($name);

            $output->write("Command {$name} is not defined.\n");

            $output->write(" Do you mean one of these?\n");

            $output->write(implode("\n", $commands));

            return 0;
        }

        return $command->run($input, $output);
    }
    public function describe(IDescriptor $descriptor, IConsoleOutput $output, array $options = []): void
    {
        $descriptor->describeApplication($this, $output, $options);
    }
    protected function boot(): void
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->getDefaultCommands() as $command) {
            $this->addCommand($command);
        }

        $this->booted = true;
    }
    protected function getDefaultCommands(): array
    {
        return [
            new HelpCommand(),
            new ListCommand()
        ];
    }
    protected function pushCommand(Command $command): void
    {
        $this->commands[strtolower($command->getName())] = $command;

        foreach ($command->getAliases() as $alias) {
            $this->commands[strtolower($alias)] = $command;
        }
    }
    protected function getCommandName(IConsoleInput $input): string
    {
        return $input->getFirstArgument() ?: $this->defaultCommand;
    }
    protected function getHelpCommand(): HelpCommand
    {
        return $this->getCommand("help");
    }
    protected function createDefaultInputItem(): InputItem
    {
        return new InputItem([
            new InputArgument("command", 'The command to execute', InputMode::Required)
        ], [
            new InputOption("help", "-h", "Display help for the given command"),
            new InputOption("version", "-v", "Display this application version"),
        ]);
    }
    protected function getDefaultHelperSet(): HelperSet
    {
        return new HelperSet();
    }
    protected function findAlternatives(string $name): array
    {
        $allCommands = isset($this->commandLoader) ? $this->commandLoader->getNames() : [];

        $allCommands = array_merge($allCommands, array_keys($this->commands));

        return $this->replaceFinder->find($name, $allCommands);
    }
    protected function getCommands(): array
    {
        if (!isset($this->commandLoader)) {
            return $this->commands;
        }

        $commands = $this->commands;

        foreach ($this->commandLoader->getNames() as $name) {
            if (!isset($commands[$name]) && $this->hasCommand($name)) {
                $commands[$name] = $this->getCommand($name);
            }
        }

        return $commands;
    }
    protected function allCommandsFor(string $namespace): array
    {
        $commands = [];

        $namespace = substr_count($namespace, ':') + 1;

        foreach ($this->commands as $name => $command) {
            if ($namespace === $this->extractNamespace($name, $namespace)) {
                $commands[$name] = $command;
            }
        }

        if (isset($this->commandLoader)) {
            foreach ($this->commandLoader->getNames() as $name) {
                if (
                    !isset($commands[$name])
                    && $namespace === $this->extractNamespace($name, $namespace)
                    && $this->hasCommand($name)
                ) {
                    $commands[$name] = $this->getCommand($name);
                }
            }
        }

        return $commands;
    }
    protected function extractNamespace(string $name, int $limit): string
    {
        $parts = explode(':', $name, -1);

        return implode(':', array_slice($parts, 0, $limit));
    }
}
