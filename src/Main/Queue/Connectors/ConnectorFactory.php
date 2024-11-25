<?php

namespace Src\Main\Queue\Connectors;

use Src\Main\Container\IContainer;
use Src\Main\Queue\Services\Database\DatabaseConnector;
use Src\Main\Queue\Services\Sync\SyncConnector;

class ConnectorFactory implements IConnectorFactory
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function make(string $name): IConnector
    {
        return match ($name) {
            "sync" => new SyncConnector($this->container),
            "database" => new DatabaseConnector($this->container),
        };
    }
}
