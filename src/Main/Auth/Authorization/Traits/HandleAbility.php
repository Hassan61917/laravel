<?php

namespace Src\Main\Auth\Authorization\Traits;

use Closure;
use Src\Main\Auth\Authentication\IAuth;

trait HandleAbility
{

    public function abilities(): array
    {
        return $this->abilities;
    }
    public function define(string $ability, Closure $callback): static
    {
        $this->abilities[$ability] = $callback;

        return $this;
    }
    public function has(string ...$abilities): bool
    {
        foreach ($abilities as $ability) {
            if (! isset($this->abilities[$ability])) {
                return false;
            }
        }

        return true;
    }
    protected function resolveAbilityCallback(string $ability, ?IAuth $user): ?Closure
    {
        if ($this->has($ability) && $this->canBeCalledWithUser($user, $this->abilities[$ability])) {
            return $this->abilities[$ability];
        }

        return null;
    }
}
