<?php

namespace Src\Main\Debug;

use Throwable;

interface ExceptionHandleable
{
    public function handleException(IExceptionOperation $operation, Throwable $e): void;
}
