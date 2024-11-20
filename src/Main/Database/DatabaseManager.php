<?php

namespace Src\Main\Database;

use RuntimeException;
use Src\Main\Container\IContainer;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Connections\IConnectionFactory;
use Src\Main\Foundation\DriverManager;

class DatabaseManager extends DriverManager implements IConnectionResolver
{
    protected string $configName = "database";
    public function __construct(
        IContainer $container,
        protected IConnectionFactory $connectionFactory
    ) {
        parent::__construct($container);
    }
    public function connection(string $name = null): Connection
    {
        return $this->getDriver($name);
    }
    public function getDefaultConnection(): string
    {
        return $this->getDefaultDriver();
    }
    public function setDefaultConnection(string $name): void
    {
        $this->setDefaultDriver($name);
    }
    public function connectUsing(string $name, array $config, bool $force = false): Connection
    {
        if ($force) {
            $this->purge($name);
        }

        if ($this->hasDriver($name)) {
            throw new RuntimeException("Cannot establish connection [$name] because another connection with that name already exists.");
        }

        $connection = $this->connectionFactory->make($name, $config);

        $this->addDriver($name, $connection);

        return $connection;
    }
    public function getConnections(): array
    {
        return array_merge(
            $this->getCachedDrivers(),
            $this->getCustomDrivers()
        );
    }
    protected function create(string $driver): Connection
    {
        $config = $this->getConfig("connections.$driver");

        return $this->connectionFactory->make($driver, $config);
    }
    protected function purge(string $name): void
    {
        $this->disconnect($name);

        $this->forgetDriver($name);
    }
    protected function disconnect(string $name): void
    {
        if ($this->hasDriver($name)) {
            $this->getCachedDriver($name)->disconnect();
        }
    }
}
