<?php

namespace Src\Main\View\Engines;

use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;

class EngineManager extends DriverManager
{
    public function __construct(
        IContainer $container,
        protected IEngineFactory $engineFactory
    ) {
        parent::__construct($container);
    }
    protected function create(string $driver): IEngine
    {
        return $this->engineFactory->make($driver);
    }
}
