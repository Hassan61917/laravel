<?php

namespace Src\Main\Routing\Route\Actions;

use Closure;

interface IActionFactory
{
    public function make(array|Closure $action): IAction;
}
