<?php

namespace Src\Main\Queue;

use Throwable;

interface IServiceJob
{
    public function getJobId(): string;
    public function payload(): array;
    public function fire(): void;
    public function release(int $delay = 0): void;
    public function isReleased(): bool;
    public function isDeleted(): bool;
    public function isDeletedOrReleased(): bool;
    public function hasFailed(): bool;
    public function delete(): void;
    public function attempts(): int;
    public function maxTries(): int;
    public function markAsFailed(): void;
    public function fail(Throwable $e = null): void;
    public function getName(): string;
    public function resolveName(): string;
    public function getQueue(): string;
    public function getConnectionName(): string;
    public function getRawBody(): string;
}
