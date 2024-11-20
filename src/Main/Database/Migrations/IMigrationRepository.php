<?php

namespace Src\Main\Database\Migrations;

interface IMigrationRepository
{
    public function setConnection(string $connection): static;
    public function createRepository(): void;
    public function repositoryExists(): bool;
    public function deleteRepository(): void;
    public function log(string $file, int $batch): void;
    public function delete(object $migration): void;
    public function getRan(): array;
    public function getMigrations(int $steps): array;
    public function getMigrationsByBatch(int $batch): array;
    public function getMigrationBatches(): array;
    public function getNextBatchNumber(): int;
    public function getLast(): array;
}
