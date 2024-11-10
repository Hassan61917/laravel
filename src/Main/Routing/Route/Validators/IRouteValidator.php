<?php

namespace Src\Main\Routing\Route\Validators;

use Src\main\Http\Request;
use Src\Main\Routing\Route\Route;

interface IRouteValidator
{
    public function isMatch(Route $route, Request $request): bool;
}
