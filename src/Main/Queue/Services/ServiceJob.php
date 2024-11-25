<?php

namespace Src\Main\Queue\Services;

use Src\Main\Container\IContainer;
use Src\Main\Queue\IJobHandler;
use Src\Main\Queue\IServiceJob;
use Throwable;

abstract class ServiceJob implements IServiceJob
{
    protected bool $deleted = false;
    protected bool $released = false;
    protected bool $failed = false;
    protected IJobHandler $jobHandler;
    public function __construct(
        protected IContainer $container,
        protected string $connectionName,
        protected ?string $queueName = null
    ) {}
    public function delete(): void
    {
        $this->deleted = true;
    }
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
    public function release(int $delay = 0): void
    {
        $this->released = true;
    }
    public function isReleased(): bool
    {
        return $this->released;
    }
    public function isDeletedOrReleased(): bool
    {
        return $this->isDeleted() || $this->isReleased();
    }
    public function markAsFailed(): void
    {
        $this->failed = true;
    }
    public function fail(Throwable $e = null): void
    {
        $this->markAsFailed();

        if ($this->isDeleted()) {
            return;
        }

        $this->delete();
    }
    public function hasFailed(): bool
    {
        return $this->failed;
    }
    public function payload(): array
    {
        return json_decode($this->getRawBody(), true);
    }
    public function getConnectionName(): string
    {
        return $this->connectionName;
    }
    public function getQueue(): string
    {
        return $this->queueName;
    }
    public function getContainer(): IContainer
    {
        return $this->container;
    }
    public function resolveName(): string
    {
        return $this->payload()['displayName'];
    }
    public function maxTries(): int
    {
        return (int) $this->payload()['max_tries'];
    }
    public function getName(): string
    {
        $class = get_class($this->getJobHandler());

        return "$class@handle";
    }
    public function fire(): void
    {
        $payload = $this->payload();

        $handler = $this->getJobHandler();

        $handler->handle($this, $payload["data"]);
    }
    protected function getJobHandler(): IJobHandler
    {
        return $this->jobHandler ??= $this->container->make(IJobHandler::class);
    }
}
