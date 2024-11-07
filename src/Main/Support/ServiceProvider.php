<?php

namespace Src\Main\Support;

use Closure;
use Src\Main\Foundation\IApplication;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;

abstract class ServiceProvider
{
    protected IObserverList $booting;
    protected IObserverList $booted;
    public function __construct(
        protected IApplication $app
    ) {
        $this->booting = new ObserverList();
        $this->booted = new ObserverList();
    }
    public function boot(): void {}
    public function getAliases(): array
    {
        return [];
    }
    public function booting(Closure $callback): void
    {
        $this->booting->add($callback);
    }
    public function booted(Closure $callback): void
    {
        $this->booted->add($callback);
    }
    public function start(): void
    {
        $this->callBooting();
        $this->boot();
        $this->callBooted();
    }
    public function callBooting(): void
    {
        $this->booting->run();
    }
    public function callBooted(): void
    {
        $this->booted->run();
    }
    public function callAfterResolving(string $name, Closure $callback): void
    {
        $this->app->afterResolving($name, $callback);

        if ($this->app->resolved($name)) {
            $callback($this->app->make($name), $this->app);
        }
    }
    public abstract function register(): void;
}
