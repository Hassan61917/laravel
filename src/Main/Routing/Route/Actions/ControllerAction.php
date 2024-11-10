<?php

namespace Src\Main\Routing\Route\Actions;

use ReflectionFunctionAbstract;
use ReflectionMethod;
use Src\Main\Routing\Route\Dispatchers\IActionDispatcher;

class ControllerAction extends Action
{
    public function __construct(
        IActionDispatcher $dispatcher,
        protected array $controller
    ) {
        parent::__construct($dispatcher);
    }
    protected function getMethod(): ReflectionFunctionAbstract
    {
        return new ReflectionMethod($this->controller[0], $this->controller[1]);
    }
}
