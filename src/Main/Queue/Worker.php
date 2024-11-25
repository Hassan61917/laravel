<?php

namespace Src\Main\Queue;

use Src\Main\Cache\ICacheRepository;
use Src\Main\Debug\ExceptionManager;
use Src\Main\Queue\Exceptions\MaxAttemptsExceededException;
use Throwable;

class Worker
{
    protected string $name;
    public bool $shouldQuit = false;
    public function __construct(
        protected QueueManager $manager,
        protected ICacheRepository $cache,
        protected ExceptionManager $exceptionManager
    ) {}
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    public function sleep(int $seconds): void
    {
        sleep($seconds);
    }
    protected function handle(string $connection, string $queue, WorkerOption $option): bool
    {
        $jobsProcessed = 0;

        while (true) {
            $job = $this->getNextJob($connection, $queue);

            if ($job) {
                $jobsProcessed++;

                if (!$this->runJob($job, $connection, $option)) {
                    return false;
                }
            }

            $this->sleep($job ? $option->rest : $option->sleep);

            $status = $this->shouldBeStopped($jobsProcessed, $job, $option);

            if ($status) {
                return true;
            }
        }
    }
    public function start(string $connection, string $queue, WorkerOption $option, bool $once = false): bool
    {
        $option->maxJob = $once ? 1 : $option->maxJob;
        return $this->handle($connection, $queue, $option);
    }
    protected function getNextJob(string $connection, string $queue): ?IServiceJob
    {
        try {
            return $this->manager->getDriver($connection)->pop($queue);
        } catch (Throwable $e) {
            $this->exceptionManager->handleReport($e);

            $this->stopWorker();

            $this->sleep(1);

            return null;
        }
    }
    protected function runJob(IServiceJob $job, string $connectionName, WorkerOption $options): bool
    {
        try {
            return $this->process($connectionName, $job, $options);
        } catch (Throwable $e) {
            $this->exceptionManager->handleReport($e);
            $this->stopWorker();
            return false;
        }
    }
    protected function process(string $connectionName, IServiceJob $job, WorkerOption $options): bool
    {
        try {
            $this->checkMaxAttempts($job, $options->maxTries);

            if (!$job->isDeleted()) {
                $job->fire();
            }

            return true;
        } catch (Throwable $e) {
            $this->exceptionManager->handleReport($e);
            return false;
        }
    }
    protected function stopWorker(): void
    {
        $this->shouldQuit = true;
    }
    protected function checkMaxAttempts(IServiceJob $job, int $maxTries): void
    {
        $maxTries = $job->maxTries() > 0 ? $job->maxTries() : $maxTries;

        if ($maxTries === 0 || $job->attempts() <= $maxTries) {
            return;
        }

        $this->failJob($job, $e = $this->maxAttemptsExceededException($job));

        throw $e;
    }
    protected function failJob(IServiceJob $job, Throwable $e): void
    {
        $job->fail($e);
    }
    protected function maxAttemptsExceededException(IServiceJob $job): MaxAttemptsExceededException
    {
        return new MAxAttemptsExceededException($job);
    }
    protected function shouldBeStopped(int $jobsProcessed, ?IServiceJob $job, WorkerOption $option): bool
    {
        if (
            $this->shouldQuit ||
            $jobsProcessed > $option->maxJob ||
            $option->stopWhenEmpty && is_null($job)
        ) {
            return true;
        }

        return false;
    }
}
