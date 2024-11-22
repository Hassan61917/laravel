<?php

namespace Src\Main\Database\Eloquent\Traits\Builder;

use Closure;
use Src\Main\Database\Eloquent\Scopes\IScope;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Database\Query\WhereClause;

trait HasScopes
{
    protected array $scopes = [];
    protected array $removedScopes = [];
    public function removedScopes(): array
    {
        return $this->removedScopes;
    }
    public function withGlobalScope(string $name, IScope $scope): static
    {
        $this->scopes[$name] = $scope;

        return $this;
    }
    public function withoutGlobalScope(string $scope): static
    {
        unset($this->scopes[$scope]);

        $this->removedScopes[] = $scope;

        return $this;
    }
    public function withoutGlobalScopes(string ...$scopes): static
    {
        if (empty($scopes)) {
            $scopes = array_keys($this->scopes);
        }

        foreach ($scopes as $scope) {
            $this->withoutGlobalScope($scope);
        }

        return $this;
    }
    public function hasNamedScope($scope): bool
    {
        return $this->model->hasNamedScope($scope);
    }
    public function applyScopes(): static
    {
        if (empty($this->scopes)) {
            return $this;
        }

        $builder = $this->clone();

        foreach ($this->scopes as $scope) {
            $builder->callScope(fn() => $scope->apply($builder, $this->getModel()));
        }

        return $builder;
    }
    public function callScopes(array $scopes = []): static
    {
        $builder = $this;

        foreach ($scopes as $name => $parameters) {

            if (is_int($name)) {
                [$name, $parameters] = [$parameters, []];
            }

            $builder = $builder->callNamedScope($name, $parameters);
        }

        return $builder;
    }
    protected function callScope(Closure $closure, array $parameters = []): static
    {
        array_unshift($parameters, $this);

        $query = $this->getQuery();

        $whereCount = count($query->wheres ?? []);

        $closure($parameters);

        if (count($query->wheres ?? []) > $whereCount) {
            $this->addNewWheresWithinGroup($query, $whereCount);
        }

        return $this;
    }
    protected function callNamedScope(string $scope, array $parameters = []): static
    {
        return $this->callScope(
            fn($params) => $this->model->callNamedScope($scope, $params),
            $parameters
        );
    }
    protected function addNewWheresWithinGroup(QueryBuilder $query, int $whereCount): void
    {
        $wheres = $query->wheres;

        $query->wheres = [];

        $this->groupWhereSliceForScope(
            $query,
            array_slice($wheres, 0, $whereCount)
        );

        $this->groupWhereSliceForScope(
            $query,
            array_slice($wheres, $whereCount)
        );
    }
    protected function groupWhereSliceForScope(QueryBuilder $query, array $wheres): void
    {
        $whereBooleans = collect($wheres)->pluck('boolean');

        if ($whereBooleans->contains(fn($logicalOperator) => str_contains($logicalOperator, 'or'))) {
            $query->wheres[] = $this->createNestedWhere(
                $wheres,
                str_replace(' not', '', $whereBooleans->first())
            );
        }
    }
    protected function createNestedWhere(array $wheres, string $boolean = 'and'): WhereClause
    {
        $query = $this->getQuery()->forNestedWhere();

        $query->wheres = $wheres;

        return $query->createWhereClause(null, null, $query, "Nested", $boolean);
    }
}
