<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use InvalidArgumentException;
use Src\Main\Events\EventDispatcher;

trait HasEvents
{
    use WithModelEvent;
    protected static EventDispatcher $dispatcher;
    protected static array $defaultObservers = [];
    protected array $observables = [];
    public static function setEventDispatcher(EventDispatcher $dispatcher): void
    {
        static::$dispatcher = $dispatcher;
    }
    public static function addDefaultObservers(string ...$observers): void
    {
        foreach ($observers as $observer) {
            static::$defaultObservers[] = $observer;
        }
    }
    public static function getDefaultObservers(): array
    {
        return array_merge(
            [
                'creating',
                'created',
                'updating',
                'updated',
                'saving',
                'saved',
                'restoring',
                'restored',
                'deleting',
                'deleted',
                'forceDeleting',
                'forceDeleted',
            ],
            self::$defaultObservers
        );
    }
    public static function observe(string ...$classes): void
    {
        foreach ($classes as $class) {
            static::registerObserver($class);
        }
    }
    protected static function registerObserver(string $class): void
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Unable to find observer: ' . $class);
        }

        foreach (self::getDefaultObservers() as $event) {
            if (method_exists($class, $event)) {
                static::registerModelEvent($event, "$class@$event");
            }
        }
    }

    protected static function getEventName(string $event): string
    {
        $name = static::class;

        return "eloquent.{$event}: {$name}";
    }
    public static function flushEventListeners(): void
    {
        if (! isset(static::$dispatcher)) {
            return;
        }

        $instance = new static;

        foreach ($instance->getObservableEvents() as $event) {
            static::$dispatcher->forget(self::getEventName($event));
        }
    }
    public function addObservableEvents(string ...$observables): void
    {
        $this->observables = array_unique(
            array_merge($this->observables, $observables)
        );
    }
    public function removeObservableEvents(string ...$observables): void
    {
        $this->observables = array_diff($this->observables, $observables);
    }
    protected function getObservableEvents(): array
    {
        return array_merge(
            $this->getDefaultEvents(),
            $this->observables
        );
    }
    protected function fireModelEvent(string $event): bool
    {
        if (! isset(static::$dispatcher)) {
            return false;
        }

        static::$dispatcher->dispatch(self::getEventName($event), [$this]);

        return true;
    }
}
