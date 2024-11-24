<?php

namespace Src\Main\Log;

use InvalidArgumentException;
use Src\Main\Log\Loggers\CustomLogger;

class LoggerFactory implements ILoggerFactory
{
    public function make(string $name, array $config = []): ILogger
    {
        if (empty($config)) {
            throw new InvalidArgumentException("Logger configuration is not defined");
        }
        return match ($name) {
            "custom" => new CustomLogger($config),
        };
    }
}
