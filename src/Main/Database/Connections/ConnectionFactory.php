<?php

namespace Src\Main\Database\Connections;

use InvalidArgumentException;
use Src\Main\Database\Connectors\IConnector;
use Src\Main\Database\Connectors\IConnectorFactory;

class ConnectionFactory implements IConnectionFactory
{
    public function __construct(
        protected IConnectorFactory $connectorFactory
    ) {}
    public function make(string $name, array $config = []): Connection
    {
        $config = $this->parseConfig($name, $config);

        return $this->resolveConnection($config);
    }
    protected function parseConfig(string $name, array $config): array
    {
        $config['name'] = $name;
        return $config;
    }
    protected function resolveConnection(array $config): Connection
    {
        $pdo = $this->createConnector($config)->connect($config);
        return $this->createConnection($pdo, $config);
    }
    protected function createConnector(array $config): IConnector
    {
        if (!isset($config['driver'])) {
            throw new InvalidArgumentException("Driver not found");
        }
        return $this->connectorFactory->make($config["driver"]);
    }
    protected  function createConnection(\PDO $pdo, array $config): Connection
    {
        $driver = $config['driver'];

        return match ($driver) {
            "mysql" => new MysqlConnection($pdo, $config)
        };
    }
}
