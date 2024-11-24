<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class View extends Facade
{
    protected static function getAccessor(): string
    {
        return 'view';
    }
}
