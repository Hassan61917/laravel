<?php

namespace Src\Main\Routing\Middlewares;

use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Routing\Router;

class RouteParameterBinding extends Middleware
{
    public function __construct(
        protected Router $router
    ) {}
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        $route = $request->route();

        $this->router->handleParameterBinding($route);

        return null;
    }
}
