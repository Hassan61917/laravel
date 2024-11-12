<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Session extends Facade
{
    protected static function getAccessor(): string
    {
        return "session";
    }
}
