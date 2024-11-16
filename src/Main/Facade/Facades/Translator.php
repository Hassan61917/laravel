<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Translator extends Facade
{
    protected static function getAccessor(): string
    {
        return "translator";
    }
}
