<?php

namespace Src\Main\Bus;

use Src\Main\Queue\QueueJob;

class PendingDispatch
{
    public function __construct(
        protected QueueJob $job
    ) {}
    public function onConnection(?string $connection): static
    {
        $this->job->onConnection($connection);

        return $this;
    }
    public function onQueue(?string $queue): static
    {
        $this->job->onQueue($queue);

        return $this;
    }
    public function delay(int $delay = 0): static
    {
        $this->job->delay($delay);

        return $this;
    }
    public function __call(string $method, array $parameters): static
    {
        $this->job->{$method}(...$parameters);

        return $this;
    }
    public function __destruct()
    {
        app(IBusDispatcher::class)->dispatch($this->job);
    }
}
