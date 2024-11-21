<?php

namespace Src\Main\Events;

use Src\Main\Support\ServiceProvider;

class EventsServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "event" => [EventDispatcher::class]
        ];
    }
    public function register(): void
    {
        $this->app->singleton("events", function ($app) {
            return new EventDispatcher($app);
        });
    }
}
