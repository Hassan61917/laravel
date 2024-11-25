<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Bus\IBusDispatcher;
use Src\Main\Facade\Facade;

class Bus extends Facade
{
    protected static function getAccessor(): string
    {
        return IBusDispatcher::class;
    }
}
