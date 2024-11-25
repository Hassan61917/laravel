<?php

namespace Src\Main\Foundation\Console\Commands\Queue;

use Src\Main\Console\AppCommand;
use Src\Main\Queue\Worker;
use Src\Main\Queue\WorkerOption;

class QueueWork extends AppCommand
{
    protected string $signature = 'queue:work
                            {connection? : The name of the queue connection to work}
                            {--name=default : The name of the worker}
                            {--queue= : The names of the queues to work}
                            {--once : Only process the next job on the queue}
                            {--stop-when-empty : Stop when the queue is empty}
                            {--backoff=0 : The number of seconds to wait before retrying a job that encountered an uncaught exception}
                            {--max-jobs= : The number of jobs to process before stopping}
                            {--max-time= : The maximum number of seconds the worker should run}
                            {--force : Force the worker to run even in maintenance mode}
                            {--sleep=3 : Number of seconds to sleep when no job is available}
                            {--rest=0 : Number of seconds to rest between jobs}
                            {--timeout=60 : The number of seconds a child process can run}
                            {--tries=1 : Number of times to attempt a job before logging it failed}';

    protected string $description = 'Start processing jobs on the queue';
    public function __construct(
        protected Worker $worker
    ) {
        parent::__construct();
    }
    public function handle(): bool
    {
        $connection = $this->getConnection();

        $queue = $this->getQueue($connection);

        return $this->runWorker($connection, $queue);
    }
    protected function getConnection(): string
    {
        return $this->getInputArgument("connection") ??
            $this->laravel['config']['queue.default'];
    }
    protected function getQueue(string $connection): string
    {
        return $this->getOption('queue') ??
            $this->laravel['config']["queue.connections.{$connection}.queue"];
    }
    protected function runWorker(string $connection, string $queue): bool
    {
        return $this->worker
            ->setName($this->getOption('name'))
            ->start(
                $connection,
                $queue,
                $this->gatherWorkerOptions(),
                (bool)$this->getOption("once"),
            );
    }
    protected function gatherWorkerOptions(): WorkerOption
    {
        return new WorkerOption(
            $this->getOption('name'),
            $this->getOption('backoff'),
            $this->getOption('max-jobs') ?? PHP_INT_MAX,
            $this->getOption('max-time') ?? PHP_INT_MAX,
            $this->getOption('rest'),
            $this->getOption('tries'),
            $this->getOption('sleep'),
            $this->getOption('timeout'),
            (bool)$this->getOption('force'),
            (bool)$this->getOption('stop-when-empty'),
        );
    }
}
