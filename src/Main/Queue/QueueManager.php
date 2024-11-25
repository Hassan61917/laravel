<?php

namespace Src\Main\Queue;

use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;
use Src\Main\Queue\Connectors\IConnectorFactory;

class QueueManager extends DriverManager implements IQueueFactory
{
    protected string $configName = "queue";

    public function __construct(
        IContainer $container,
        protected IConnectorFactory $connectorFactory
    ) {
        parent::__construct($container);
    }

    public function make(string $name = null): IQueueService
    {
        return $this->getDriver($name);
    }
    protected function create(string $driver): IQueueService
    {
        $config = $this->getConfig("connections.$driver");

        return $this->connectorFactory
            ->make($driver)
            ->connect($config)
            ->setConnectionName($driver);
    }
}
