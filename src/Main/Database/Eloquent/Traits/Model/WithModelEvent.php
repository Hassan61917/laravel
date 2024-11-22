<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Closure;

trait WithModelEvent
{
    protected static function registerModelEvent(string $event, string|Closure $callback): void
    {
        if (isset(static::$dispatcher)) {
            static::$dispatcher->listen(
                self::getEventName($event),
                $callback
            );
        }
    }
    public static function creating(Closure $callback): void
    {
        static::registerModelEvent('creating', $callback);
    }
    public static function created(Closure $callback): void
    {
        static::registerModelEvent('created', $callback);
    }
    public static function saving(Closure $callback): void
    {
        static::registerModelEvent('saving', $callback);
    }
    public static function saved(Closure $callback): void
    {
        static::registerModelEvent('saved', $callback);
    }
    public static function updating(Closure $callback): void
    {
        static::registerModelEvent('updating', $callback);
    }
    public static function updated(Closure $callback): void
    {
        static::registerModelEvent('updated', $callback);
    }
    public static function deleting(Closure $callback): void
    {
        static::registerModelEvent('deleting', $callback);
    }
    public static function deleted(Closure $callback): void
    {
        static::registerModelEvent('deleted', $callback);
    }
}
