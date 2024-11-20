<?php

namespace Src\Main\Database\Query\Traits;

use Src\Main\Pagination\LengthAwarePaginator;
use Src\Main\Pagination\Paginator;

trait WithPagination
{
    public function paginate(int $perPage = 10, array $columns = ['*'], string $pageName = 'page'): LengthAwarePaginator
    {
        $page = Paginator::resolveCurrentPage($pageName);

        $total = $this->getCountForPagination();

        $items = $total > 0 ? $this->forPage($page, $perPage)->get($columns) : collect();

        return $this->paginator($items, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function simplePaginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?string $page = null): Paginator
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $this->offset(($page - 1) * $perPage)->limit($perPage + 1);

        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function getCountForPagination(array $columns = ['*']): int
    {
        $results = $this->runPaginationCountQuery($columns);

        if (!isset($results[0])) {
            return 0;
        } elseif (is_object($results[0])) {
            return (int) $results[0]->aggregate;
        }

        return (int) array_change_key_case((array) $results[0])['aggregate'];
    }
    protected function runPaginationCountQuery(array $columns = ['*']): array
    {
        if (isset($this->groups)) {
            $clone = $this->cloneForPaginationCount();

            if (!isset($this->columns) && count($this->joins)) {
                $clone->select([$this->from . '.*']);
            }

            $sql = "({$clone->toSql()}) as {$this->grammar->wrap('aggregate_table')}";

            return $this->newQuery()
                ->from($sql)
                ->mergeBindings($clone)
                ->setAggregate('count', $this->withoutSelectAliases($columns))
                ->get()->all();
        }

        $without = ['columns', 'orders', 'limit', 'offset'];

        return $this->cloneWithout($without)
            ->cloneWithoutBindings(['select', 'order'])
            ->setAggregate('count', $this->withoutSelectAliases($columns))
            ->get()->all();
    }
    protected function cloneForPaginationCount(): static
    {
        return $this->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order']);
    }
    protected function withoutSelectAliases(array $columns): array
    {
        return array_map(function ($column) {
            $aliasPosition = stripos($column, ' as ');
            return is_string($column) && $aliasPosition
                ? substr($column, 0, $aliasPosition) : $column;
        }, $columns);
    }
}
