<?php

namespace Src\Main\Foundation\Providers;

use Src\Main\Debug\DebugServiceProvider;
use Src\Main\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    protected array $providers = [
        DebugServiceProvider::class
    ];
    public function register(): void
    {
        $this->registerProviders();
    }
    protected function registerProviders(): void
    {
        foreach ($this->providers as $provider) {
            $this->app->register(new $provider($this->app));
        }
    }
}
