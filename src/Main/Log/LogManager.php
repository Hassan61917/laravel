<?php

namespace Src\Main\Log;

use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;

class LogManager extends DriverManager
{
    protected string $configName = "log";
    public function __construct(
        IContainer $container,
        protected ILoggerFactory $loggerFactory,
    ) {
        parent::__construct($container);
    }
    public function debug(string $message = null, array $context = []): static
    {
        $this->getDriver()->debug($message, $context);

        return $this;
    }
    protected function create(string $driver): ILogger
    {
        $config = $this->getConfig("loggers.$driver");
        return $this->loggerFactory->make($driver, $config);
    }
}
