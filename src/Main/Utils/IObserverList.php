<?php

namespace Src\Main\Utils;

use Closure;

interface IObserverList extends \Countable
{
    public function add(Closure $closure): void;
    public function append(string $key, Closure $closure): void;
    public function getAll(string $key = null): array;
    public function run(array $args = [], string $key = null): void;
    public function reset(): void;
}
