<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;

class Crypt extends Facade
{
    protected static function getAccessor(): string
    {
        return "encryptor";
    }
}
