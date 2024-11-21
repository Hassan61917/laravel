<?php

namespace Src\Main\Foundation\Providers;

use Src\Main\Events\DiscoverEvents;
use Src\Main\Facade\Facades\Event;
use Src\Main\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->booting(function () {
            $events = $this->getEvents();

            foreach ($events as $event => $listeners) {
                foreach ($listeners as $listener) {
                    Event::listen($event, $listener);
                }
            }
        });
    }
    protected function getEvents(): array
    {
        return $this->discoverEvents();
    }
    protected function discoverEvents(): array
    {
        $result = [];
        foreach ($this->discoverEventsWithin() as $path) {
            if (is_dir($path)) {
                $events = DiscoverEvents::within($path, base_path());
                $result = array_merge_recursive($result, $events);
            }
        }
        return $result;
    }
    protected function discoverEventsWithin(): array
    {
        return  [
            $this->app->path('Listeners'),
        ];
    }
}
