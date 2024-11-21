<?php

namespace Src\Main\Events;

abstract class Event
{
    public static function dispatch(mixed ...$args): void
    {
        event(new static(...$args));
    }
    public static function dispatchIf(bool $condition, mixed ...$args): void
    {
        if ($condition) {
            event(new static(...$args));
        }
    }
}
