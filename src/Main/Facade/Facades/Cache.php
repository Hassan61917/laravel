<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Cache extends Facade
{
    protected static function getAccessor(): string
    {
        return "cache";
    }
}
