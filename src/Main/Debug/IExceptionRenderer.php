<?php

namespace Src\Main\Debug;

use Throwable;

interface IExceptionRenderer
{
    public function render(Throwable $exception): string;
}
