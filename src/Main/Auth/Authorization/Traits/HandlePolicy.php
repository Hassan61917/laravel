<?php

namespace Src\Main\Auth\Authorization\Traits;

use Closure;
use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Auth\Authorization\Policy;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Utils\Str;

trait HandlePolicy
{
    protected array $policyPaths = [
        "App\\Policies",
        "App\\Models\\Policies"
    ];
    public function policies(): array
    {
        return $this->policies;
    }
    public function addPolicyPath(string $path): static
    {
        $this->policyPaths[] = $path;

        return $this;
    }
    public function addPolicy(string $class, string $policy): static
    {
        $this->policies[$class] = $policy;

        return $this;
    }
    public function hasPolicy(string $class): bool
    {
        return isset($this->policies[$class]);
    }
    public function getPolicy(Model $model): ?Policy
    {
        $class = get_class($model);

        if (!$this->hasPolicy($class) && $policy = $this->guessPolicyName($class)) {
            $this->addPolicy($class, $policy);
        }

        if ($this->hasPolicy($class)) {
            return $this->resolvePolicy($this->policies[$class]);
        }

        foreach ($this->policies as $expected => $policy) {
            if (is_subclass_of($class, $expected)) {
                return $this->resolvePolicy($policy);
            }
        }

        return null;
    }
    protected function resolvePolicy(string $class): Policy
    {
        return $this->container->make($class);
    }
    protected function guessPolicyName(string $class): ?string
    {
        $class = class_basename($class);

        foreach ($this->policyPaths as $path) {
            $policy = "$path\\{$class}Policy";

            if (class_exists($policy)) {
                return $policy;
            }
        }

        return null;
    }
    protected function resolvePolicyCallback(string $ability, ?IAuth $user, Model $model): ?Closure
    {
        $policy = $this->getPolicy($model);

        $ability = $this->formatAbilityToMethod($ability);

        if (is_null($policy) || ! is_callable([$policy, $ability])) {
            return null;
        }

        return function () use ($user, $ability, $model, $policy) {

            $result = $this->callPolicyBefore($ability, $policy, $user, $model);

            if (!$result) {
                return false;
            }

            return $this->callPolicyMethod($policy, $ability, $user, $model);
        };
    }
    protected function callPolicyBefore(string $ability, Policy $policy, ?IAuth $user, Model $model): bool
    {
        if ($this->canBeCalledWithUser($user, $policy, 'before')) {
            return $policy->before($ability, $user, $model);
        }

        return true;
    }
    protected function callPolicyMethod(Policy $policy, string $method, ?IAuth $user, Model $model): bool
    {
        if (! is_callable([$policy, $method])) {
            return false;
        }

        if ($this->canBeCalledWithUser($user, $policy, $method)) {
            return $policy->{$method}($user, $model);
        }

        return false;
    }
    protected function formatAbilityToMethod(string $ability): string
    {
        return str_contains($ability, '-') ? Str::camel($ability) : $ability;
    }
}
