<?php

namespace Src\Main\Queue\Services\Database;

use Src\Main\Container\IContainer;
use Src\Main\Queue\Connectors\IConnector;
use Src\Main\Queue\IQueueService;

class DatabaseConnector implements IConnector
{
    public function __construct(
        protected IContainer $container,
    ) {}
    public function connect(array $config): IQueueService
    {
        return new DatabaseQueue($this->container, $config);
    }
}
