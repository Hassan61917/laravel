<?php

namespace Src\Main\Queue\Traits;

use Src\Main\Queue\QueueJob;

trait Queueable
{
    public string $queue = "default";
    public ?string $connection;
    public int $delay = 0;

    public function onConnection(?string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }
    public function onQueue(string $name): static
    {
        $this->queue = $name;

        return $this;
    }
    public function delay(int $seconds): static
    {
        $this->delay = $seconds;

        return $this;
    }
    protected function serializeJob(QueueJob $job): string
    {
        return serialize($job);
    }
}
