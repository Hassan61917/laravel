<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Route extends Facade
{
    protected static function getAccessor(): string
    {
        return "router";
    }
}
