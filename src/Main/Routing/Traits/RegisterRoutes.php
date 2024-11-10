<?php

namespace Src\Main\Routing\Traits;

use Closure;
use Src\Main\Routing\Route\Actions\IAction;
use Src\Main\Routing\Route\Actions\IActionFactory;
use Src\Main\Routing\Route\Route;

trait RegisterRoutes
{
    public function get(string $uri, array|Closure $action, array $data = []): Route
    {
        return $this->addRoute('GET', $uri, $action, $data);
    }
    public function post(string $uri, array|Closure $action, array $data = []): Route
    {
        return $this->addRoute('POST', $uri, $action, $data);
    }
    public function put(string $uri, array|Closure $action, array $data = []): Route
    {
        return $this->addRoute('PUT', $uri, $action, $data);
    }
    public function delete(string $uri, array|Closure $action, array $data = []): Route
    {
        return $this->addRoute('DELETE', $uri, $action, $data);
    }
    protected function addRoute(string $method, string $uri, array|Closure $action, array $data = []): Route
    {
        $action = $this->container->make(IActionFactory::class)->make($action);

        $route = $this->createRoute($method, $uri, $action, $data);


        return $this->routes->add($route);
    }
    protected function createRoute(string $method, string $uri, IAction $action, array $data): Route
    {
        $route = $this->newRoute($method, $uri, $action, $data);

        if ($this->hasGroupData()) {
            $this->mergeWithRoute($route);
        }

        return $route;
    }
    protected function newRoute(string $method, string $uri, IAction $action, array $data): Route
    {
        $route = new Route($this->prefix($uri), $method,  $action, $data);

        $route->setContainer($this->container);

        return $route;
    }
}
