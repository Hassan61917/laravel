<?php

namespace Src\Main\Queue\Services\Database;

use Src\Main\Container\IContainer;
use Src\Main\Queue\Services\ServiceJob;

class DatabaseJob extends ServiceJob
{
    public function __construct(
        protected DatabaseQueue $database,
        protected DatabaseJobRecord $job,
        IContainer $container,
        string $connectionName,
        ?string $queueName = null
    ) {
        parent::__construct($container, $connectionName, $queueName);
    }
    public function release($delay = 0): void
    {
        parent::release($delay);

        $this->database->deleteAndRelease($this, $delay, $this->queueName);
    }
    public function delete(): void
    {
        parent::delete();

        $this->database->deleteReserved($this->job->id);
    }
    public function getJobId(): string
    {
        return $this->job->id;
    }
    public function attempts(): int
    {
        return (int) $this->job->attempts;
    }
    public function getRawBody(): string
    {
        return $this->job->payload;
    }
    public function getJobRecord(): DatabaseJobRecord
    {
        return $this->job;
    }
}
