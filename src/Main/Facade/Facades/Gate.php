<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;
class Gate extends Facade
{
    protected static function getAccessor(): string
    {
        return "gate";
    }
}