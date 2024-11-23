<?php

namespace Src\Main\Auth\Authorization;

use App\Models\User;
use Src\Main\Database\Eloquent\Model;

abstract class Policy
{
    public function before(string $ability, User $user, Model $model): bool
    {
        if (!$this->beforeAll($user, $model)) {
            return false;
        }

        return $this->beforeAbility($ability, $user, $model);
    }
    protected function beforeAbility(string $ability, User $user, Model $model): bool
    {
        return true;
    }
    protected function beforeAll(User $user, Model $model): bool
    {
        return true;
    }
}
