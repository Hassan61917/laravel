<?php

namespace Src\Main\Database\Traits;

use Generator;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Src\Main\Pagination\LengthAwarePaginator;
use Src\Main\Pagination\Paginator;

trait BuildsQueries
{
    public function chunk(int $count, callable $callback): bool
    {
        $page = 1;

        while (true) {
            $results = $this->forPage($page, $count)->get();

            if (count($results) == 0) {
                break;
            }

            if (!$callback($results, $page)) {
                return false;
            }

            $page++;
        };

        return true;
    }
    public function each(callable $callback, int $count = 100): bool
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
            return true;
        });
    }
    public function lazy(int $chunkSize = 100): Generator
    {
        if ($chunkSize < 1) {
            throw new InvalidArgumentException('The chunk size should be at least 1');
        }

        $page = 1;

        while (true) {
            $results = $this->forPage($page++, $chunkSize)->get();

            foreach ($results as $result) {
                yield $result;
            }

            if (count($results) < $chunkSize) {
                return;
            }
        }
    }
    public function first(array $columns = ['*']): ?object
    {
        return $this->take(1)->get($columns)->first();
    }
    protected function paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options = []): LengthAwarePaginator
    {
        return new LengthAwarePaginator($items, $total, $perPage, $currentPage, $options);
    }
    protected function simplePaginator(Collection $items, int $perPage, int $currentPage, array $options = []): Paginator
    {
        return new Paginator($items, $perPage, $currentPage, $options);
    }
}
