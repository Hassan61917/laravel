<?php

namespace Src\Main\Database\Migrations;

use Src\Main\Database\Connections\Connection;
use Src\Main\Database\IConnectionResolver;
use Src\Main\Database\Query\QueryBuilder;

class MigrationRepository implements IMigrationRepository
{
    protected string $connection;
    public function __construct(
        protected IConnectionResolver $resolver,
        protected string $table,
    ) {}
    public function setConnection(string $connection): static
    {
        $this->connection = $connection;

        return $this;
    }
    public function resolveConnection(): Connection
    {
        return $this->resolver->connection($this->connection);
    }
    public function getConnectionResolver(): IConnectionResolver
    {
        return $this->resolver;
    }
    public function getRan(): array
    {
        return $this->table()
            ->orderBy('batch', 'asc')
            ->orderBy('migration', 'asc')
            ->get()->pluck("migration")->all();
    }
    public function getMigrations(int $steps): array
    {
        $query = $this->table()->where('batch', '>=', '1');

        return $query
            ->orderBy('batch', 'desc')
            ->orderBy('migration', 'desc')
            ->take($steps)->get()->all();
    }
    public function getMigrationsByBatch(int $batch): array
    {
        return $this->table()
            ->where('batch', $batch)
            ->orderBy('migration', 'desc')
            ->get()
            ->all();
    }
    public function getLast(): array
    {
        $query = $this->table()->where('batch', $this->getLastBatchNumber());

        return $query->orderBy('migration', 'desc')->get()->all();
    }
    public function getMigrationBatches(): array
    {
        return $this->table()
            ->orderBy('batch', 'asc')
            ->orderBy('migration', 'asc')
            ->get()->pluck("batch", "migration")->all();
    }
    public function createRepository(): void
    {
        $schema = $this->resolveConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            $table->increments('id');
            $table->string('migration');
            $table->bigInteger('batch');
        });
    }
    public function repositoryExists(): bool
    {
        $schema = $this->resolveConnection()->getSchemaBuilder();

        return $schema->hasTable($this->table);
    }
    protected function table(): QueryBuilder
    {
        return $this->resolveConnection()->table($this->table);
    }
    public function deleteRepository(): void
    {
        $schema = $this->resolveConnection()->getSchemaBuilder();

        $schema->drop($this->table);
    }
    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }
    public function getLastBatchNumber(): int
    {
        return $this->table()->max('batch');
    }
    public function log(string $file, int $batch): void
    {
        $record = ['migration' => $file, 'batch' => $batch];

        $this->table()->insert($record);
    }
    public function delete(object $migration): void
    {
        $this->table()->where('migration', $migration->migration)->delete();
    }
}
