<?php

namespace Src\Main\Log;

interface ILoggerFactory
{
    public function make(string $name, array $config = []): ILogger;
}
