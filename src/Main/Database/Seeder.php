<?php

namespace Src\Main\Database;

use InvalidArgumentException;
use Src\Main\Console\AppCommand;
use Src\Main\Container\IContainer;

abstract class Seeder
{
    protected static array $called = [];
    protected IContainer $container;
    protected AppCommand $command;
    public function setContainer(IContainer $container): static
    {
        $this->container = $container;

        return $this;
    }
    public function setCommand(AppCommand $command): static
    {
        $this->command = $command;

        return $this;
    }
    public function call(string $class, array $parameters = []): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class {$class} does not exist.");
        }
        $seeder = $this->resolveSeeder($class);

        $name = get_class($seeder);

        if (isset($this->command)) {
            $this->command->getOutput()->write("{$name} RUNNING \n");

            $startTime = microtime(true);

            $seeder->__invoke($parameters);

            $runTime = number_format((microtime(true) - $startTime) * 1000);

            $this->command->getOutput()->write("{$name} {$runTime}ms Done \n");
        } else {
            $seeder->__invoke($parameters);
        }

        static::$called[] = $class;
    }
    public function callSeeders(string ...$classes): void
    {
        foreach ($classes as $class) {
            $this->call($class);
        }
    }
    protected function resolveSeeder(string $class): Seeder
    {
        if (isset($this->container)) {
            $instance = $this->container->make($class);

            $instance->setContainer($this->container);
        } else {
            $instance = new $class;
        }

        if (isset($this->command)) {
            $instance->setCommand($this->command);
        }

        return $instance;
    }
    public function __invoke(array $parameters = []): void
    {
        if (! method_exists($this, 'run')) {
            throw new InvalidArgumentException('Method [run] missing from ' . get_class($this));
        }

        if (isset($this->container)) {
            $this->container->call([$this, 'run'], $parameters);
        } else {
            $this->run(...$parameters);
        }
    }
}
