<?php

namespace Src\Main\Queue\Services\Sync;

use Src\Main\Container\IContainer;
use Src\Main\Queue\Services\ServiceJob;

class SyncJob extends ServiceJob
{
    public function __construct(
        protected string $payload,
        IContainer $container,
        ?string $connectionName = null,
        ?string $queueName = null,
    ) {
        parent::__construct($container, $connectionName, $queueName);
    }
    public function getJobId(): string
    {
        return '';
    }
    public function attempts(): int
    {
        return 1;
    }
    public function getRawBody(): string
    {
        return $this->payload;
    }
    public function getQueue(): string
    {
        return "sync";
    }
}
