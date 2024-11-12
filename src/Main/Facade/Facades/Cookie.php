<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Cookie extends Facade
{
    protected static function getAccessor(): string
    {
        return 'cookie';
    }
}
