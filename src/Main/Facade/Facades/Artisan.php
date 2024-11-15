<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;
use Src\Main\Foundation\Console\IConsoleKernel;

class Artisan extends Facade
{
    protected static function getAccessor(): string
    {
        return IConsoleKernel::class;
    }
}
