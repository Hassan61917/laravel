<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Queue extends Facade
{
    protected static function getAccessor(): string
    {
        return "queue";
    }
}
