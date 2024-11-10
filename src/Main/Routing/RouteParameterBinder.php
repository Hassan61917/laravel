<?php

namespace Src\Main\Routing;

use Src\Main\Container\IContainer;
use Src\Main\Routing\Route\Route;

class RouteParameterBinder implements IRouteParameterBinder
{
    public function __construct(
        protected IContainer $container
    ){}
    public function resolve(Route $route): Route
    {
        $parameters = $route->getParameters();

        $actionParameters = $route->getAction()->getParameters();

        foreach ($parameters as $name => $parameter) {
            if (array_key_exists($name, $actionParameters)) {
                $className = $actionParameters[$name]->className;
                $class = $this->container->make($className);
                $route->replaceParameter($name, $class);
            }
        }

        return $route;
    }
}
