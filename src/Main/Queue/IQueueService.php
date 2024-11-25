<?php

namespace Src\Main\Queue;

interface IQueueService
{
    public function pop(string $name): ?IServiceJob;
    public function size(string $name): int;
    public function push(QueueJob $job, string $name, int $delay = 0): void;
    public function pushAll(array $jobs, string $name, int $delay = 0): void;
    public function clear(string $queue): bool;
    public function getConnectionName(): string;
    public function setConnectionName(string $name): static;
}
