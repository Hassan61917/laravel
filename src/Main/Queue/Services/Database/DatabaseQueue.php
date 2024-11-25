<?php

namespace Src\Main\Queue\Services\Database;

use Src\Main\Container\IContainer;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Queue\IServiceJob;
use Src\Main\Queue\QueueJob;
use Src\Main\Queue\Services\QueueService;
use Src\Main\Support\Traits\InteractsWithTime;

class DatabaseQueue extends QueueService
{
    use InteractsWithTime;

    protected string $table;
    protected Connection $database;
    public function __construct(
        protected IContainer $container,
        protected array $config = []
    ) {
        parent::__construct($container);
        $this->init();
    }
    public function getDatabase(): Connection
    {
        return $this->database;
    }
    public function push(QueueJob $job, string $name, int $delay = 0): void
    {
        $payload = $this->createPayload($job);

        $this->pushToDatabase($payload, $name, $delay);
    }
    public function size(string $name): int
    {
        return $this->getTable()
            ->where('queue', $this->getQueue($name))
            ->count();
    }
    public function clear(string $queue): bool
    {
        return (bool) $this->getTable()
            ->where('queue', $this->getQueue($queue))
            ->delete();
    }
    public function pop(string $name): ?IServiceJob
    {
        $name = $this->getQueue($name);

        $job = $this->getNextAvailableJob($name);

        if ($job) {
            return $this->toJob($name, $job);
        }

        return null;
    }
    public function release(DatabaseJobRecord $job, string $queue, int $delay): void
    {
        $this->pushToDatabase($job->payload, $queue, $delay, $job->attempts);
    }
    public function deleteAndRelease(DatabaseJob $job, string $queue, int $delay): void
    {
        $table = $this->getTable();

        if ($table->find($job->getJobId())) {
            $table->where('id', $job->getJobId())->delete();
        }

        $this->release($job->getJobRecord(), $queue, $delay);
    }
    public function deleteReserved(string $id): void
    {
        $table = $this->getTable();

        if ($table->find($id)) {
            $table->where('id', $id)->delete();
        }
    }
    protected function init(): void
    {
        $this->setDatabase();
        $this->setTable();
    }
    protected function setDatabase(): void
    {
        $this->database = $this->container->make('db.connection');
    }
    protected function setTable(): void
    {
        $this->table = $this->config["table"];
    }
    protected function getTable(): QueryBuilder
    {
        return $this->database->table($this->table);
    }
    protected function pushToDatabase(string $payload, string $queue, int $delay, int $attempts = 0): int
    {
        $record = $this->buildDatabaseRecord(
            $payload,
            $queue,
            $this->availableAt($delay),
            $attempts
        );

        return $this->getTable()->insertGetId($record);
    }
    protected function buildDatabaseRecord(string $payload, string $queue, int $availableAt, int $attempts = 0): array
    {
        return [
            'payload' => $payload,
            'queue' => $queue,
            'available_at' => $availableAt,
            'attempts' => $attempts,
            'reserved_at' => null,
            'created_at' => $this->currentTime(),
        ];
    }
    protected function getQueue(?string $queue): string
    {
        return $queue ?: "default";
    }
    protected function getNextAvailableJob(string $queue): ?DatabaseJobRecord
    {
        $job = $this->getTable()
            ->where('queue', $queue)
            ->whereNull(['reserved_at'])
            ->where('available_at', '<=', $this->currentTime())
            ->orderBy('id', 'asc')
            ->first();

        return $job ? new DatabaseJobRecord($job) : null;
    }
    protected function toJob(string $queue, DatabaseJobRecord $job): DatabaseJob
    {
        $job = $this->markJobAsReserved($job);

        return new DatabaseJob($this, $job, $this->container, $this->connectionName, $queue);
    }
    protected function markJobAsReserved(DatabaseJobRecord $job): DatabaseJobRecord
    {
        $this->getTable()
            ->where('id', $job->id)
            ->update(['reserved_at' => $job->touch(), 'attempts' => $job->increment()]);

        return $job;
    }
}
