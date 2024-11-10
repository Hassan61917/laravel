<?php

namespace Src\Main\Routing;

use Src\Main\Routing\Route\Route;

class RouteCollection extends AbstractRouteCollection
{
    protected array $routes = [];
    protected array $namedRoutes = [];

    public function add(Route $route): Route
    {
        return $this->addRoute($route);
    }
    public function refresh(): void
    {
        $this->namedRoutes = [];

        foreach ($this->getAllRoutes() as $route) {
            $this->addToNamedRoutes($route->getName(), $route);
        }
    }
    public function getByMethod(string $method): array
    {
        return $this->routes[$method] ?? [];
    }
    public function hasNamedRoute(string $name): bool
    {
        return $this->getByName($name) != null;
    }
    public function getByName(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }
    public function getAllRoutes(): array
    {
        $result = [];

        foreach ($this->routes as $routes) {
            foreach ($routes as $route) {
                $result[] = $route;
            }
        }

        return $result;
    }
    public function getNamedRoutes(): array
    {
        return $this->namedRoutes;
    }
    protected function addRoute(Route $route): Route
    {
        $method = $route->getMethod();
        $uri = $route->getUri();
        $this->addToRoutes($method, $uri, $route);
        $this->addToNamedRoutes($route->getName(), $route);
        return $route;
    }
    protected function addToRoutes(string $method, string $uri, Route $route): void
    {
        $this->routes[$method][$uri] = $route;
    }
    protected function addToNamedRoutes(string $name, Route $route): void
    {
        if ($name == "") {
            return;
        }

        $this->namedRoutes[$name] = $route;
    }
}
