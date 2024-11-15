<?php

namespace Src\Main\Foundation\Providers;

use Src\Main\Console\CommandFinder;
use Src\Main\Console\ICommandFinder;
use Src\Main\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    protected array $providers = [];
    public function register(): void
    {
        $this->registerCommandFinder();
        $this->registerProviders();
    }
    protected function registerCommandFinder(): void
    {
        $this->app->singleton(ICommandFinder::class, CommandFinder::class);
    }
    protected function registerProviders(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register(new $provider($this->app));
        }
    }
}
