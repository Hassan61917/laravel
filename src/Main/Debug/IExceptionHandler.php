<?php

namespace Src\Main\Debug;

use Throwable;

interface IExceptionHandler
{
    public function handle(ExceptionHandleable $item, Throwable $e): void;
}
