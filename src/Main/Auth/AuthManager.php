<?php

namespace Src\Main\Auth;

use Src\Main\Auth\Authentication\Guards\IGuardFactory;
use Src\Main\Auth\Authentication\IGuard;
use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;

class AuthManager extends DriverManager
{
    protected string $configName = "auth";
    public function __construct(
        IContainer $container,
        protected IGuardFactory $guardFactory
    ) {
        parent::__construct($container);
    }
    public function setDefaultDriver(string $name): void
    {
        $this->config["defaults"]["guard"] = $name;
    }
    public function getDefaultDriver(): ?string
    {
        return $this->config["defaults"]["guard"] ?? null;
    }
    protected function create(string $driver): IGuard
    {
        $config = $this->getConfig("guards.$driver");

        return $this->guardFactory->make($driver, $config);
    }
}
