<?php

namespace Src\Main\Routing\Route\Actions;

use Src\Main\Routing\Route\IActionResult;
use Src\Main\Routing\Route\Route;

interface IAction
{
    public function handle(Route $route): IActionResult;
    public function getParameters(): array;
}
