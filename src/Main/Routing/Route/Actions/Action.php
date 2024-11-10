<?php

namespace Src\Main\Routing\Route\Actions;

use ReflectionFunctionAbstract;
use ReflectionParameter;
use Src\Main\Routing\Route\Dispatchers\IActionDispatcher;
use Src\Main\Routing\Route\IActionResult;
use Src\Main\Routing\Route\Route;
use Src\Main\Utils\ReflectionHelper;

abstract class Action implements IAction
{
    public function __construct(
        protected IActionDispatcher $dispatcher
    ) {}
    public function setDispatcher(IActionDispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }
    public function getParameters(): array
    {
        $method = $this->getMethod();
        return $this->findParameters(...$method->getParameters());
    }
    public function handle(Route $route): IActionResult
    {
        return $this->dispatcher->dispatch($route);
    }
    protected function findParameters(ReflectionParameter ...$parameters): array
    {
        $result = [];

        foreach ($parameters as $param) {
            $name = $param->getName();
            $result[$name] = new ActionParameter(
                $name,
                ReflectionHelper::getClassName($param),
                $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
            );
        }

        return $result;
    }
    protected abstract function getMethod(): ReflectionFunctionAbstract;
}
