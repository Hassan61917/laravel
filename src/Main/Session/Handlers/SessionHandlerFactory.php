<?php

namespace Src\Main\Session\Handlers;

use SessionHandlerInterface;
use Src\Main\Container\IContainer;
use Src\Main\Filesystem\Filesystem;

class SessionHandlerFactory implements ISessionHandlerFactory
{
    public function __construct(
        protected IContainer $container,
    ) {}
    public function make(string $name, array $config): SessionHandlerInterface
    {
        return match ($name) {
            "file" => $this->createFileHandler($config),
            "cookie" => $this->createCookieHandler($config),
            "array" => $this->createArrayHandler($config),
             "database" => $this->createDatabaseHandler($config)
        };
    }
    protected function createFileHandler(array $config): FileSessionHandler
    {
        return new FileSessionHandler(
            new Filesystem(),
            $config["files"],
            $config["lifetime"],
        );
    }
    protected function createCookieHandler(array $config): CookieSessionHandler
    {
        $cookie = $this->container->make("cookie");
        return new CookieSessionHandler(
            $cookie,
            $config["lifetime"],
            $config["expire_on_close"]
        );
    }
    protected function createArrayHandler(array $config): ArraySessionHandler
    {
        return new ArraySessionHandler($config["lifetime"]);
    }
    protected function createDatabaseHandler(array $config): DatabaseSessionHandler
    {
        return new DatabaseSessionHandler(
            $this->container["db.connection"],
            $config["table"],
            $config["lifetime"],
            $this->container
        );
    }
}
