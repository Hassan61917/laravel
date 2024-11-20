<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class DB extends Facade
{
    protected static function getAccessor(): string
    {
        return "db";
    }
}
