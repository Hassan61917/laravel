<?php

namespace Src\Main\Queue\Services\Sync;

use Src\Main\Container\IContainer;
use Src\Main\Queue\IServiceJob;
use Src\Main\Queue\QueueJob;
use Src\Main\Queue\Services\QueueService;
use Throwable;

class SyncQueue extends QueueService
{
    public function __construct(
        protected IContainer $container
    ) {
        parent::__construct($container);
    }
    public function pop(string $name = null): ?IServiceJob
    {
        return null;
    }
    public function size(string $name = null): int
    {
        return 0;
    }
    public function push(QueueJob $job, string $name, int $delay = 0): void
    {
        $this->executeJob($job, $name);
    }
    public function clear(string $queue): bool
    {
        return true;
    }
    protected function executeJob(QueueJob $job, string $name = null): void
    {
        $payload = $this->createPayload($job);

        $serviceJob = $this->resolveJob($payload, $name);

        try {
            $serviceJob->fire();
        } catch (Throwable $e) {
            $this->handleException($serviceJob, $e);
        }
    }
    protected function resolveJob(string $payload, string $name): IServiceJob
    {
        return new SyncJob($payload, $this->container, $this->connectionName, $name);
    }
    protected function handleException(IServiceJob $queueJob, Throwable $e)
    {
        $queueJob->fail($e);

        throw $e;
    }
}
