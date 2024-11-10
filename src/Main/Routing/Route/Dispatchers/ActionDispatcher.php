<?php

namespace Src\Main\Routing\Route\Dispatchers;

use Closure;
use Src\Main\Container\IContainer;
use Src\Main\Routing\Route\IActionResult;
use Src\Main\Routing\Route\Route;

class ActionDispatcher implements IActionDispatcher
{
    public function __construct(
        protected IContainer    $container,
        protected array|Closure $action
    ) {}
    public function dispatch(Route $route): IActionResult
    {
        $action = $this->action instanceof Closure ? [$this->action] : $this->action;
        return $this->container->call($action, $route->getParameters());
    }
}
