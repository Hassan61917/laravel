<?php

namespace Src\Main\Session;

use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;
use Src\Main\Session\Handlers\ISessionHandlerFactory;

class SessionManager extends DriverManager
{
    protected string $configName = "session";

    public function __construct(
        IContainer $container,
        protected ISessionHandlerFactory $handlerFactory
    ) {
        parent::__construct($container);
    }

    protected function create(string $driver): ISessionStore
    {
        $handler = $this->handlerFactory->make($driver, $this->config);

        return new SessionStore($this->config["cookie"], $handler);
    }
}
