<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Hash extends Facade
{
    protected static function getAccessor(): string
    {
        return "hash";
    }
}
