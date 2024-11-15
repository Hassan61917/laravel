<?php

namespace Src\Main\Console;

use Closure;
use Src\Main\Foundation\IApplication;
use Src\Symfony\Console\Application;
use Src\Symfony\Console\Commands\Command;

class ConsoleApplication extends Application
{
    protected static array $bootstraps = [];
    protected array $commandMap = [];
    public function __construct(
        protected IApplication $app
    ) {
        parent::__construct("Command Line App", "1.0.0");
        $this->bootstrap();
    }
    public static function addBootstrap(Closure $callback): void
    {
        static::$bootstraps[] = $callback;
    }
    public static function forgetBootstraps(): void
    {
        static::$bootstraps = [];
    }
    public function addCommand(Command $command): ?Command
    {
        if ($command instanceof AppCommand) {
            $command->setLaravel($this->app);
        }

        return parent::addCommand($command);
    }
    public function resolve(string $command): void
    {
        if (is_subclass_of($command, Command::class)) {
            $this->commandMap[CommandHelper::createName(basename($command))] = $command;
        }
    }
    public function resolveCommands(array $commands): static
    {
        foreach ($commands as $command) {
            $this->resolve($command);
        }

        return $this;
    }
    public function setApplicationCommandLoader(): static
    {
        $this->setCommandLoader(new CommandLoader($this->app, $this->commandMap));

        return $this;
    }
    public function getApplication(): IApplication
    {
        return $this->app;
    }
    protected function bootstrap(): void
    {
        foreach (static::$bootstraps as $bootstrap) {
            $bootstrap($this);
        }
    }
}
