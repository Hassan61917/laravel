<?php

namespace Src\Main\Database\Query\Processors;

class MySqlQueryProcessor extends QueryProcessor
{
    public function processColumns(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $result->name,
                'type_name' => $result->type_name,
                'type' => $result->type,
                'collation' => $result->collation,
                'nullable' => $result->nullable === 'YES',
                'default' => $result->default,
                'auto_increment' => $result->extra === 'auto_increment',
            ];
        }, $results);
    }
    public function processIndexes(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;

            return [
                'name' => $name = strtolower($result->name),
                'columns' => explode(',', $result->columns),
                'type' => strtolower($result->type),
                'unique' => (bool) $result->unique,
                'primary' => $name === 'primary',
            ];
        }, $results);
    }
    public function processForeignKeys(array $results): array
    {
        return array_map(function ($result) {
            $result = (object) $result;
            return [
                'name' => $result->name,
                'columns' => explode(',', $result->columns),
                'foreign_schema' => $result->foreign_schema,
                'foreign_table' => $result->foreign_table,
                'foreign_columns' => explode(',', $result->foreign_columns),
                'on_update' => strtolower($result->on_update),
                'on_delete' => strtolower($result->on_delete),
            ];
        }, $results);
    }
}
