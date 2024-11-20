<?php

namespace Src\Main\Database\Query\Processors;

use Src\Main\Database\Query\QueryBuilder;

abstract class QueryProcessor
{
    public function processSelect(QueryBuilder $query, array $results): array
    {
        return $results;
    }
    public function processInsertGetId(QueryBuilder $query, string $sql, array $values, string $sequence = null): int
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }
    public function processTables(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'size' => isset($result->size) ? (int) $result->size : null,
                'collation' => $result->collation ?? null,
            ];
        }, $results);
    }
    public function processViews(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'definition' => $result->definition,
            ];
        }, $results);
    }
    public function processTypes(array $results): array
    {
        return $results;
    }
    public function processColumns(array $results): array
    {
        return $results;
    }
    public function processIndexes(array $results): array
    {
        return $results;
    }
    public function processForeignKeys(array $results): array
    {
        return $results;
    }
}
