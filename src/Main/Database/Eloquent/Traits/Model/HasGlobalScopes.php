<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Closure;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Scopes\ClosureScope;
use Src\Main\Database\Eloquent\Scopes\IScope;

trait HasGlobalScopes
{
    protected static array $globalScopes = [];
    public static function getAllGlobalScopes(): array
    {
        return static::$globalScopes;
    }
    public static function addGlobalScope(string $scope, Closure|IScope $implementation): IScope
    {
        if ($implementation instanceof Closure) {
            $implementation = new ClosureScope($implementation);
        }

        return static::$globalScopes[static::class][$scope] = $implementation;
    }
    public static function addGlobalScopes(array $scopes): void
    {
        foreach ($scopes as $key => $scope) {
            static::addGlobalScope($key, $scope);
        }
    }
    public static function getGlobalScope(string $scope): ?IScope
    {
        return self::$globalScopes[static::class][$scope] ?? null;
    }
    public static function hasGlobalScope(string $scope): bool
    {
        return static::getGlobalScope($scope) != null;
    }
    public static function clearGlobalScopes(): void
    {
        static::$globalScopes = [];
    }
    public function getGlobalScopes(): array
    {
        return self::$globalScopes[static::class] ?? [];
    }
    public function registerGlobalScopes(EloquentBuilder $builder): EloquentBuilder
    {
        foreach ($this->getGlobalScopes() as $name => $scope) {
            $builder->withGlobalScope($name, $scope);
        }

        return $builder;
    }
    public function hasNamedScope(string $scope): bool
    {
        return method_exists($this, $this->getNameScope($scope));
    }
    public function callNamedScope(string $scope, array $parameters = []): mixed
    {
        $method = $this->getNameScope($scope);

        return $this->$method(...$parameters);
    }
    protected function getNameScope(string $scope): string
    {
        return 'scope' . ucfirst($scope);
    }
}
