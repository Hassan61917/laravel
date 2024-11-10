<?php

namespace Src\Main\Routing;
use Src\Main\Routing\Route\Route;

interface IRouteParameterBinder
{
    public function resolve(Route $route): Route;
}