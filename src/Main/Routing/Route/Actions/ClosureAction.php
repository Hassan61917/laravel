<?php

namespace Src\Main\Routing\Route\Actions;

use Closure;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use Src\Main\Routing\Route\Dispatchers\IActionDispatcher;

class ClosureAction extends Action
{
    public function __construct(
        IActionDispatcher $dispatcher,
        protected Closure $controller
    ) {
        parent::__construct($dispatcher);
    }
    protected function getMethod(): ReflectionFunctionAbstract
    {
        return new ReflectionFunction($this->controller);
    }
}
