<?php

namespace Src\Main\Events;

use Closure;
use Src\Main\Container\IContainer;
use Src\Main\Events\Listeners\ClosureListener;
use Src\Main\Events\Listeners\IListener;
use Src\Main\Events\Listeners\ClassListener;

class EventDispatcher
{
    protected array $events = [];
    protected array $listeners = [];
    public function __construct(
        protected IContainer $container
    ) {}
    public function listen(string $event, string|Closure $listener): void
    {
        $this->listeners[$event][] = $listener;
    }
    public function hasListeners(string $event): bool
    {
        return isset($this->listeners[$event]);
    }
    public function getListeners(?string $event = null): array
    {
        if (is_null($event)) {
            return $this->listeners;
        }

        return $this->prepareListeners($event);
    }
    public function dispatch(string|object $event, array $payload = []): static
    {
        [$event, $payload] = $this->parseEventAndPayload($event, $payload);

        $this->invokeListeners($event, $payload);

        return $this;
    }
    public function forget(string $event): void
    {
        unset($this->listeners[$event]);
    }
    protected function prepareListeners(string $eventName): array
    {
        $listeners = [];

        foreach ($this->listeners[$eventName] ?? [] as $listener) {
            $listeners[] = $this->makeListener($listener);
        }

        return $listeners;
    }
    protected function makeListener(string|Closure $listener): IListener
    {
        if (is_string($listener)) {
            return $this->createClassListener($listener);
        }

        return $this->createClosureListener($listener);
    }
    protected function createClassListener(string $listener): IListener
    {
        return new ClassListener($this->container, $listener);
    }
    protected function createClosureListener(Closure $listener): IListener
    {
        return new ClosureListener($listener);
    }
    protected function parseEventAndPayload(string|object $event, array $payload = []): array
    {
        if (is_string($event)) {
            return [$event, $payload];
        }

        return [get_class($event), [$event]];
    }
    protected function invokeListeners(string $event, array $payload = []): void
    {
        foreach ($this->getListeners($event) as $listener) {
            $listener->execute($payload[0]);
        }
    }
}
