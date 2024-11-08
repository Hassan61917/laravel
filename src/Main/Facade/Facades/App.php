<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class App extends Facade
{
    protected static function getAccessor(): string
    {
        return "app";
    }
}
