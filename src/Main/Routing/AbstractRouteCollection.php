<?php

namespace Src\Main\Routing;

use Src\Main\Http\Exceptions\NotFoundException;
use Src\Main\Http\Request;
use Src\Main\Routing\Route\Route;

abstract class AbstractRouteCollection implements IRouteCollection
{
    public function match(Request $request): Route
    {
        $requestMethod = $request->getMethod();

        $routes = $this->getByMethod($requestMethod);

        $route = $this->findRoute($request, ...$routes);

        return $this->handleMatchedRoute($route, $request);
    }
    protected function handleMatchedRoute(?Route $route, Request $request): Route
    {
        if (is_null($route)) {
            throw new NotFoundException("The page {$request->path()} could not be found.");
        }

        return $route->bind($request);
    }
    protected function findRoute(Request $request, Route ...$routes): ?Route
    {
        foreach ($routes as $route) {
            if ($route->isMatch($request)) {
                return $route;
            }
        }
        return null;
    }
}
