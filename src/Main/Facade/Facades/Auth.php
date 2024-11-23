<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;
class Auth extends Facade
{
    protected static function getAccessor(): string
    {
        return 'auth';
    }
}
