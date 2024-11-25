<?php

namespace Src\Main\Bus;

use Src\Main\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerDispatcher();
    }
    protected function registerDispatcher(): void
    {
        $this->app->singleton(IBusDispatcher::class, BusDispatcher::class);
    }
}
