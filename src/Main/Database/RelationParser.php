<?php

namespace Src\Main\Database;

use Closure;
use Src\Main\Database\Eloquent\Relations\BelongsToMany;

class RelationParser implements IRelationParser
{
    public function parse(array $relations): array
    {
        return $this->parseWithRelations($relations);
    }
    protected function parseWithRelations(array $relations): array
    {
        if ($relations === []) {
            return [];
        }

        $relations = $this->prepareNestedWithRelationships($relations);

        $results = [];

        foreach ($relations as $name => $constraints) {

            $results = $this->addNestedWiths($name, $results);

            $results[$name] = $constraints;
        }

        return $results;
    }
    protected function prepareNestedWithRelationships(array $relations, string $prefix = ''): array
    {
        $preparedRelationships = [];

        if ($prefix !== '') {
            $prefix .= '.';
        }

        foreach ($relations as $key => $value) {
            if (is_numeric($key) && is_string($value)) {
                [$key, $value] = $this->parseNameAndAttributeSelectionConstraint($value);
            }

            $key = $prefix . $key;

            $preparedRelationships[$key] = $this->combineConstraints([
                $value,
                $preparedRelationships[$key] ?? function () {},
            ]);
        }

        return $preparedRelationships;
    }
    protected function parseNameAndAttributeSelectionConstraint(string $name): array
    {
        return str_contains($name, ':')
            ? $this->createSelectWithConstraint($name)
            : [$name, function () {}];
    }
    protected function createSelectWithConstraint(string $name): array
    {
        return [explode(':', $name)[0], static function ($query) use ($name) {
            $query->select(array_map(static function ($column) use ($query) {
                if (str_contains($column, '.')) {
                    return $column;
                }

                return $query instanceof BelongsToMany
                    ? $query->getRelated()->getTable() . '.' . $column
                    : $column;
            }, explode(',', explode(':', $name)[1])));
        }];
    }
    protected function combineConstraints(array $constraints): Closure
    {
        return function ($builder) use ($constraints) {
            foreach ($constraints as $constraint) {
                $builder = $constraint($builder) ?? $builder;
            }

            return $builder;
        };
    }
    protected function addNestedWiths(string $name, array $results): array
    {
        $progress = [];

        foreach (explode('.', $name) as $segment) {
            $progress[] = $segment;

            if (! isset($results[$last = implode('.', $progress)])) {
                $results[$last] =  function () {};
            }
        }

        return $results;
    }
}
