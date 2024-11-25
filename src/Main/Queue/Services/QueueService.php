<?php

namespace Src\Main\Queue\Services;

use Src\Main\Container\IContainer;
use Src\Main\Queue\IPayloadCreator;
use Src\Main\Queue\IQueueService;
use Src\Main\Queue\QueueJob;

abstract class QueueService implements IQueueService
{
    protected string $connectionName;
    protected IPayloadCreator $payloadCreator;
    public function __construct(
        protected IContainer $container
    ) {}
    public function setConnectionName(string $name): static
    {
        $this->connectionName = $name;

        return $this;
    }
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }
    public function getContainer(): IContainer
    {
        return $this->container;
    }
    public function pushAll(array $jobs, string $name = null, int $delay = 0): void
    {
        foreach ($jobs as $job) {
            $this->push($job, $name, $delay);
        }
    }
    protected function getPayloadCreator(): IPayloadCreator
    {
        return $this->payloadCreator ??= $this->container->make(IPayloadCreator::class);
    }
    protected function createPayload(QueueJob $job): string
    {
        return $this->getPayloadCreator()->create($job);
    }
}
