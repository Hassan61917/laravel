<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Event extends Facade
{
    protected static function getAccessor(): string
    {
        return "events";
    }
}
