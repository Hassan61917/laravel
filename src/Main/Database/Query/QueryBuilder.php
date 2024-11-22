<?php

namespace Src\Main\Database\Query;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Query\Grammars\QueryGrammar;
use Src\Main\Database\Query\Processors\QueryProcessor;
use Src\Main\Database\Query\Traits\WithAggregate;
use Src\Main\Database\Query\Traits\WithCrud;
use Src\Main\Database\Query\Traits\WithJoins;
use Src\Main\Database\Query\Traits\WithOrders;
use Src\Main\Database\Query\Traits\WithPagination;
use Src\Main\Database\Query\Traits\WithWheres;
use Src\Main\Database\Traits\BuildsQueries;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;

class QueryBuilder
{
    use WithCrud,
        WithWheres,
        WithJoins,
        WithOrders,
        WithAggregate,
        WithPagination,
        BuildsQueries;

    public array $bindings = [
        "select" => [],
        'where' => [],
        'join' => [],
        "order" => []
    ];
    public array $operators = [
        '=',
        '<',
        '>',
        '<=',
        '>=',
        '!=',
        'like',
        'not like',
        '&',
        '|',
        'is',
        'is not',
    ];
    public string $from;
    public int $limit;
    public int $offset;
    public bool $distinct = false;
    public array $columns;
    public IObserverList $beforeQueryCallbacks;
    public function __construct(
        protected Connection $connection,
        protected QueryGrammar $grammar,
        protected QueryProcessor $processor
    ) {
        $this->beforeQueryCallbacks = new ObserverList();
    }
    public function defaultKeyName(): string
    {
        return 'id';
    }
    public function getConnection(): Connection
    {
        return $this->connection;
    }
    public function getGrammar(): QueryGrammar
    {
        return $this->grammar;
    }
    public function getProcessor(): QueryProcessor
    {
        return $this->processor;
    }
    public function getRawBindings(): array
    {
        return $this->bindings;
    }
    public function beforeQuery(Closure $callback): static
    {
        $this->beforeQueryCallbacks->add($callback);

        return $this;
    }
    public function applyBeforeQueryCallbacks(): void
    {
        $this->beforeQueryCallbacks->run([$this]);

        $this->beforeQueryCallbacks->reset();
    }
    public function newQuery(): static
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }
    public function select(array $columns = ["*"]): static
    {
        if (empty($columns)) {
            $columns[] = "*";
        }

        $this->columns = $columns;

        $this->setBindings([], "select");

        return $this;
    }
    public function addSelect(array $columns = []): static
    {
        if (!isset($this->columns)) {
            $this->columns = [];
        }

        array_push($this->columns, ...$columns);

        return $this;
    }
    public function from(string $table, string $as = null): static
    {
        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }
    public function offset(int $value): static
    {
        $this->offset = max(0, $value);

        return $this;
    }
    public function limit(int $value): static
    {
        $this->limit = max($value, 0);

        return $this;
    }
    public function skip(int $value): static
    {
        return $this->offset($value);
    }
    public function take(int $value): static
    {
        return $this->limit($value);
    }
    public function forPage(int $page, int $perPage = 15): QueryBuilder
    {
        return $this->offset(($page - 1) * $perPage)->limit($perPage);
    }
    public function exists(): bool
    {
        $this->applyBeforeQueryCallbacks();

        $results = $this->connection->select(
            $this->grammar->compileExists($this),
            $this->getBindings()
        );


        if (isset($results[0])) {
            $results = (array) $results[0];

            return (bool) $results['exists'];
        }

        return false;
    }
    public function doesNotExist(): bool
    {
        return !$this->exists();
    }
    public function truncate(): void
    {
        $this->applyBeforeQueryCallbacks();

        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
            $this->connection->statement($sql, $bindings);
        }
    }
    public function distinct(): static
    {
        $columns = func_get_args();

        if (count($columns) > 0) {
            $this->distinct = is_array($columns[0]) || is_bool($columns[0]) ? $columns[0] : $columns;
        } else {
            $this->distinct = true;
        }

        return $this;
    }
    public function toSql(): string
    {
        $this->applyBeforeQueryCallbacks();

        return $this->grammar->compileSelect($this);
    }
    public function cursor(): \Generator
    {
        if (empty($this->columns)) {
            $this->columns = ['*'];
        }

        return yield from $this->connection->cursor($this->toSql(), $this->getBindings());
    }
    public function find(string $id, array $columns = ['*']): ?object
    {
        return $this->where('id', '=', $id)->first($columns);
    }
    public function clone(): static
    {
        return clone $this;
    }
    public function mergeBindings(QueryBuilder $query): static
    {
        $this->bindings = array_merge_recursive($this->bindings, $query->bindings);

        return $this;
    }
    public function getColumns(): array
    {
        return !empty($this->columns)
            ? array_map(fn($column) => $this->grammar->getValue($column), $this->columns)
            : [];
    }
    protected function setBindings(array $bindings, string $type): static
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $this->bindings[$type] = $bindings;

        return $this;
    }
    protected function addBinding(string $type, mixed $value): static
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = array_values(array_merge($this->getBinding($type), $value));

        $this->setBindings($value, $type);

        return $this;
    }
    protected function getBinding(string $type): array
    {
        return $this->bindings[$type];
    }
    protected function runSelect(): array
    {
        return $this->connection->select(
            $this->toSql(),
            $this->getBindings(),
        );
    }
    protected function getBindings(): array
    {
        return Arr::flatten($this->bindings);
    }
    protected function resolveIncrement(string $column, int $amount, bool $increment = true, array $extra = []): string
    {
        $operator = $increment ? '+' : '-';

        $value = "{$this->grammar->wrap($column)} {$operator} {$amount}";

        return $this->update($extra, [$column => $value]);
    }
}
