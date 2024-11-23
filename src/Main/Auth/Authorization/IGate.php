<?php

namespace Src\Main\Auth\Authorization;

use Closure;
use Src\Main\Database\Eloquent\Model;

interface IGate
{
    public function has(string ...$abilities): bool;
    public function define(string $ability, Closure $callback): static;
    public function addPolicy(string $class, string $policy): static;
    public function before(Closure $callback): static;
    public function after(Closure $callback): static;
    public function allows(string $ability, Model $model): bool;
    public function denies(string $ability, Model $model): bool;
    public function check(array $abilities, Model $model): bool;
    public function any(array $abilities, Model $model): bool;
    public function authorize(string $ability, Model $model): Response;
    public function inspect(string $ability, Model $model): Response;
    public function raw(string $ability, Model $model): bool;
    public function getPolicy(Model $model): ?Policy;
}
