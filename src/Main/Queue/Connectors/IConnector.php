<?php

namespace Src\Main\Queue\Connectors;

use Src\Main\Queue\IQueueService;

interface IConnector
{
    public function connect(array $config): IQueueService;
}
