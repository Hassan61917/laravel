<?php

namespace Src\Main\Bus\Traits;

use Src\Main\Bus\IBusDispatcher;
use Src\Main\Bus\PendingDispatch;

trait Dispatchable
{
    public static function dispatch(mixed ...$arguments): PendingDispatch
    {
        $job = new static(...$arguments);

        return new PendingDispatch($job);
    }
    public static function dispatchSync(...$arguments): void
    {
        $job = new static(...$arguments);

        app(IBusDispatcher::class)->dispatchSync($job);
    }
}
