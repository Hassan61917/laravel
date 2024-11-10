<?php

namespace Src\Main\Routing\Route\Actions;

use Closure;
use Src\Main\Container\IContainer;
use Src\Main\Routing\Route\Dispatchers\ActionDispatcher;

class ActionFactory implements IActionFactory
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function make(array|Closure $action): IAction
    {
        $dispatcher = new ActionDispatcher($this->container, $action);

        if ($action instanceof Closure) {
            return new ClosureAction($dispatcher, $action);
        }

        return new ControllerAction($dispatcher, $action);
    }
}
