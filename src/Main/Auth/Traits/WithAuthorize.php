<?php

namespace Src\Main\Auth\Traits;

use Src\Main\Database\Eloquent\Model;

trait WithAuthorize
{
    public function can(array $abilities, Model $model): bool
    {
        return app('gate')->forUser($this)->check($abilities, $model);
    }
    public function canAny(array $abilities, Model $model): bool
    {
        return app('gate')->forUser($this)->any($abilities, $model);
    }
    public function cant(array $abilities, Model $model): bool
    {
        return ! $this->can($abilities, $model);
    }
    public function cannot(array $abilities, Model $model): bool
    {
        return $this->cant($abilities, $model);
    }
}
