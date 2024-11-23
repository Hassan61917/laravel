<?php

namespace Src\Main\Auth\Authorization\Traits;

use Closure;
use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Database\Eloquent\Model;

trait HandleUser
{
    protected function resolveUser(): ?IAuth
    {
        return $this->user ??= $this->container["auth.driver"]->user();
    }
    protected function callAuthCallback(?IAuth $user, string $ability, Model $model): bool
    {
        $callback = $this->resolveAuthCallback($user, $ability, $model);

        return $callback($user, $model);
    }
    protected function resolveAuthCallback(?IAuth $user, string $ability, Model $model): Closure
    {
        $policyCallback = $this->resolvePolicyCallback($ability, $user, $model);

        if ($policyCallback) {
            return $policyCallback;
        }

        $abilityCallback = $this->resolveAbilityCallback($ability, $user);

        if ($abilityCallback) {
            return $abilityCallback;
        }

        return function () {
            return false;
        };
    }
    protected function canBeCalledWithUser(?IAuth $user, object|string $class, ?string $method = null): bool
    {
        $class = is_object($class) ? get_class($class) : $class;

        if ($user) {
            return true;
        }

        if ($method) {
            return $this->methodAllowsGuests($class, $method);
        }

        return $this->callbackAllowsGuests($class);
    }
}
