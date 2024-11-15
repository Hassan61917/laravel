<?php

namespace Src\Main\Console;

use Src\Main\Container\IContainer;
use Src\Symfony\Console\Commands\Command;
use Src\Symfony\Console\Exceptions\CommandNotFoundException;
use Src\Symfony\Console\Loaders\ICommandLoader;

class CommandLoader implements ICommandLoader
{
    public function __construct(
        protected IContainer $container,
        protected array $commandMap = []
    ) {}
    public function get(string $name): Command
    {
        try {
            return $this->container->make($this->commandMap[$name]);
        } catch (\Exception $e) {
            throw new CommandNotFoundException("Command {$name} does not exist.");
        }
    }
    public function has(string $name): bool
    {
        return $name && isset($this->commandMap[$name]);
    }
    public function getNames(): array
    {
        return array_keys($this->commandMap);
    }
}
