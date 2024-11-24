<?php

namespace Src\Main\Debug;

use Src\Main\Console\AppCommand;
use Src\Main\Http\Request;
use Throwable;

interface IExceptionOperation
{
    public function handleRequest(Request $request, Throwable $e): void;
    public function handleCommand(AppCommand $command, Throwable $e): void;
}
