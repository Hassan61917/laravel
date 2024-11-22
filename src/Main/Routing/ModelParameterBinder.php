<?php

namespace Src\Main\Routing;

use Src\Main\Container\IContainer;
use Src\Main\Routing\Route\Route;

class ModelParameterBinder implements IRouteParameterBinder
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function resolve(Route $route): Route
    {
        $parameters = $route->getParameters();

        $actionParameters = $route->getAction()->getParameters();

        foreach ($parameters as $name => $parameter) {
            if (array_key_exists($name, $actionParameters)) {
                $className = $actionParameters[$name]->className;
                $class = $this->container->make($className);
                if ($class instanceof IRouteParameter) {
                    $value = $parameter["value"];
                    $field = $parameter["field"];
                    $route->replaceParameter($name, $class->resolveRouteBinding($value, $field));
                }
            }
        }

        return $route;
    }
}
