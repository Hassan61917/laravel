<?php

namespace Src\Main\Database;

use Src\Main\Database\Connections\Connection;

abstract class BaseGrammar
{
    protected Connection $connection;
    protected string $tablePrefix = "";

    public function setConnection(Connection $connection): static
    {
        $this->connection = $connection;

        return $this;
    }
    public function getConnection(): Connection
    {
        return $this->connection;
    }
    public function setTablePrefix(string $tablePrefix): static
    {
        $this->tablePrefix = $tablePrefix;

        return $this;
    }
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }
    public function wrapArray(array $values): array
    {
        return array_map(fn($v) => $this->wrap($v), $values);
    }
    public function wrapTable(string $table): string
    {
        if (str_contains($table, ' as ')) {
            return $this->wrapAliasedTable($table);
        }
        if (str_contains($table, '.')) {
            $table = substr_replace($table, '.' . $this->tablePrefix, strrpos($table, '.'), 1);

            return implode(
                '.',
                array_map(
                    fn($v) => $this->wrapValue($v),
                    explode('.', $table)
                )
            );
        }

        return $this->wrapValue($this->tablePrefix . $table);
    }
    public function wrap(string $value): string
    {
        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value);
        }

        return $this->wrapSegments(explode('.', $value));
    }
    public function parseColumns(array $columns): string
    {
        $columns = array_map(fn($v) => $this->wrap($v), $columns);

        return implode(', ', $columns);
    }
    public function parameter(?string $value): string
    {
        return '?';
    }
    public function parseParameters(array $values): string
    {
        $values = array_map(fn($v) => $this->parameter($v), $values);

        return implode(', ', $values);
    }
    public function getValue(string $column): string
    {
        return $column;
    }
    public function quoteString(string|array $value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, __FUNCTION__], $value));
        }

        return "'$value'";
    }
    protected function wrapAliasedTable(string $value): string
    {
        return $this->wrapAlias($value, $this->tablePrefix);
    }
    protected function wrapAliasedValue(string $value): string
    {
        return $this->wrapAlias($value);
    }
    protected function wrapAlias(string $value, string $prefix = ""): string
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        return $this->wrap($segments[0]) . ' as ' . $this->wrapValue($prefix . $segments[1]);
    }
    protected function wrapValue(string $value): string
    {
        if ($value !== '*') {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    }
    protected function wrapSegments(array $segments): string
    {
        $parts = [];
        if (count($segments) > 1) {
            $parts[] = $this->wrapTable(array_shift($segments));
        }
        foreach ($segments as $segment) {
            $parts[] = $this->wrapValue($segment);
        }
        return implode(".", $parts);
    }
}
