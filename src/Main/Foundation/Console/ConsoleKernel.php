<?php

namespace Src\Main\Foundation\Console;

use Closure;
use Src\Main\Console\ClosureCommand;
use Src\Main\Console\ConsoleApplication;
use Src\Main\Console\ICommandFinder;
use Src\Main\Foundation\IApplication;
use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Outputs\IConsoleOutput;

class ConsoleKernel implements IConsoleKernel
{
    protected bool $commandsLoaded = false;
    protected string $defaultCommandsPath;
    protected array $commands = [];
    protected array $commandPaths = [];
    protected array $commandRoutePaths = [];
    protected array $bootstraps = [];
    protected ConsoleApplication $artisan;
    public function __construct(
        protected IApplication $app,
    ) {
        $this->addCommandPaths($this->getDefaultCommandsPath());
    }
    public function addCommandPaths(string ...$paths): static
    {
        $this->commandPaths = array_values(array_unique(array_merge($this->commandPaths, $paths)));

        return $this;
    }
    public function addCommandRoutePaths(string ...$paths): static
    {
        $this->commandRoutePaths = array_values(array_unique(array_merge($this->commandRoutePaths, $paths)));

        return $this;
    }
    public function setDefaultCommandsPath(string $defaultCommandsPath): void
    {
        $this->defaultCommandsPath = $defaultCommandsPath;
    }
    public function getDefaultCommandsPath(): string
    {
        if (!isset($this->defaultCommandsPath)) {
            $this->defaultCommandsPath = __DIR__ . "/Commands";
        }
        return $this->defaultCommandsPath;
    }
    public function isCommandsLoaded(): bool
    {
        return $this->commandsLoaded;
    }
    public function command(string $signature, Closure $callback): ClosureCommand
    {
        $command = new ClosureCommand($signature, $callback);

        ConsoleApplication::addBootstrap(fn($artisan) => $artisan->addCommand($command));

        return $command;
    }
    public function handle(IConsoleInput $input, IConsoleOutput $output): int
    {
        $this->bootstrap();

        return $this->getArtisan()->run($input, $output);
    }
    public function terminate(IConsoleInput $input, int $status): void
    {
        $this->app->terminate();
    }
    protected function bootstrap(): void
    {
        $this->app->bootstrap($this->bootstraps);

        if (!$this->isCommandsLoaded()) {
            $this->discoverCommands();
            $this->commandsLoaded();
        }
    }
    protected function discoverCommands(): void
    {
        $commands = $this->findCommands();

        $this->resolveCommands($commands);

        $this->resolveRouteCommands();
    }
    public function findCommands(): array
    {
        $finder = $this->app->make(ICommandFinder::class);

        return $finder->find($this->commandPaths);
    }
    protected function resolveCommands(array $commands): void
    {
        ConsoleApplication::addBootstrap(
            fn(ConsoleApplication $artisan) => $artisan->resolveCommands($commands)
        );
    }
    protected function resolveRouteCommands(): void
    {
        foreach ($this->commandRoutePaths as $path) {
            if (file_exists($path)) {
                require $path;
            }
        }
    }
    protected function commandsLoaded(): void
    {
        $this->commandsLoaded = true;
    }
    protected function getArtisan(): ConsoleApplication
    {
        if (!isset($this->artisan)) {
            $this->artisan = $this->createArtisan();
        }
        return $this->artisan;
    }
    protected function createArtisan(): ConsoleApplication
    {
        $app = new ConsoleApplication($this->app);

        $app->resolveCommands($this->commands)
            ->setApplicationCommandLoader();

        return $app;
    }
}
