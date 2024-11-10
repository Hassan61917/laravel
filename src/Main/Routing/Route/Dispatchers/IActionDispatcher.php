<?php

namespace Src\Main\Routing\Route\Dispatchers;

use Src\Main\Routing\Route\IActionResult;
use Src\Main\Routing\Route\Route;

interface IActionDispatcher
{
    public function dispatch(Route $route): IActionResult;
}
