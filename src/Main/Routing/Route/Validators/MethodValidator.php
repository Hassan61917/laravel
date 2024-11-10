<?php

namespace Src\Main\Routing\Route\Validators;

use Src\main\Http\Request;
use Src\Main\Routing\Route\Route;

class MethodValidator implements IRouteValidator
{
    public function isMatch(Route $route, Request $request): bool
    {
        return $route->getMethod() == $request->getMethod();
    }
}
