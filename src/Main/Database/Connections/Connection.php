<?php

namespace Src\Main\Database\Connections;

use Closure;
use DateTimeInterface;
use PDO;
use PDOStatement;
use Src\Main\Database\Exceptions\QueryException;
use Src\Main\Database\Query\Grammars\QueryGrammar;
use Src\Main\Database\Query\Processors\QueryProcessor;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Database\Schema\Builders\SchemaBuilder;
use Src\Main\Database\Schema\Grammars\SchemaGrammar;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;

abstract class Connection
{
    protected int $fetchMode = PDO::FETCH_OBJ;
    protected bool $recordsModified = false;
    protected string $database;
    protected string $tablePrefix = "";
    protected IObserverList $beforeExecutingCallbacks;
    protected QueryProcessor $queryProcessor;
    protected QueryGrammar $queryGrammar;
    protected SchemaGrammar $schemaGrammar;
    public function __construct(
        protected PDO $pdo,
        protected array $config
    ) {
        $this->database = $this->config['database'];
        $this->tablePrefix = $this->config['prefix'];
        $this->init();
    }
    public function getConfig(): array
    {
        return $this->config;
    }
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
    public function setPdo(PDO $pdo): static
    {
        $this->pdo = $pdo;

        return $this;
    }
    public function disconnect(): static
    {
        unset($this->pdo);

        return $this;
    }
    public function beforeExecuting(Closure $callback): static
    {
        $this->beforeExecutingCallbacks->add($callback);

        return $this;
    }
    public function select(string $query, array $bindings = []): array
    {
        return $this->run($query, $bindings, function ($query, $bindings) {

            $statement = $this->execute($query, $bindings);

            return $statement->fetchAll();
        });
    }
    public function selectOne(string $query, array $bindings = []): object
    {
        $records = $this->select($query, $bindings);

        return array_shift($records);
    }
    public function cursor(string $query, array $bindings = []): \Generator
    {
        $statement = $this->run(
            $query,
            $bindings,
            fn($query, $bindings) => $this->execute($query, $bindings)
        );

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }
    public function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR,
            );
        }
    }
    public function prepareBindings(array $bindings): array
    {
        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format("Y-m-d H:i:s");
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }
    public function insert(string $query, array $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }
    public function update(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }
    public function delete(string $query, array $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }
    public function statement(string $query, array $bindings = []): bool
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $this->execute($query, $bindings);

            $this->recordsHaveBeenModified(true);

            return $this->hasModifiedRecords();
        });
    }
    public function affectingStatement($query, $bindings = []): int
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            $statement = $this->execute($query, $bindings);

            $count = $statement->rowCount();

            $this->recordsHaveBeenModified($count > 0);

            return $count;
        });
    }
    public function recordsHaveBeenModified(bool $value): void
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }
    public function hasModifiedRecords(): bool
    {
        return $this->recordsModified;
    }
    public function getOption(string $option = null): ?string
    {
        return $this->config[$option] ?? null;
    }
    public function getName(): string
    {
        return $this->getOption('name');
    }
    public function getDriverName(): string
    {
        return $this->getOption('driver');
    }
    public function getDatabaseName(): string
    {
        return $this->database;
    }
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }
    public function getQueryProcessor(): QueryProcessor
    {
        return $this->queryProcessor;
    }
    public function getQueryGrammar(): QueryGrammar
    {
        return $this->queryGrammar;
    }
    public function getSchemaGrammar(): SchemaGrammar
    {
        return $this->schemaGrammar;
    }
    public function query(): QueryBuilder
    {
        return new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getQueryProcessor()
        );
    }
    public function table(string $table, string $as = null): QueryBuilder
    {
        return $this->query()->from($table, $as);
    }
    protected function init(): void
    {
        $this->beforeExecutingCallbacks = new ObserverList();
        $this->useDefaultQueryProcessor();
        $this->useDefaultQueryGrammar();
        $this->useDefaultSchemaGrammar();
    }
    protected function useDefaultQueryProcessor(): void
    {
        $this->queryProcessor = $this->getDefaultQueryProcessor();
    }
    protected function useDefaultQueryGrammar(): void
    {
        $grammar = $this->getDefaultQueryGrammar();

        $grammar->setConnection($this);

        $this->queryGrammar = $grammar;
    }
    protected function useDefaultSchemaGrammar(): void
    {
        $grammar = $this->getDefaultSchemaGrammar();

        $grammar->setConnection($this);

        $this->schemaGrammar = $grammar;
    }
    protected function run(string $query, array $bindings, Closure $closure)
    {
        $this->beforeExecutingCallbacks->run([$query, $bindings, $this]);

        return $this->runQueryCallback($query, $bindings, $closure);
    }
    protected function runQueryCallback(string $query, array $bindings, Closure $callback)
    {
        try {
            return $callback($query, $bindings);
        } catch (\Exception $exception) {
            throw new QueryException($query, $bindings, $exception);
        }
    }
    protected function execute(string $query, array $bindings): PDOStatement
    {
        $statement = $this->prepare($query);

        $this->bindValues($statement, $this->prepareBindings($bindings));

        $statement->execute();

        return $statement;
    }
    protected function prepare(string $query): PDOStatement
    {
        $statement = $this->getPdo()->prepare($query);

        $statement->setFetchMode($this->fetchMode);

        return $statement;
    }
    protected abstract function getDefaultQueryProcessor(): QueryProcessor;
    protected abstract function getDefaultQueryGrammar(): QueryGrammar;
    protected abstract function getDefaultSchemaGrammar(): SchemaGrammar;
    public abstract function getSchemaBuilder(): SchemaBuilder;
}
