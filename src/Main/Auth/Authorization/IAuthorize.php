<?php

namespace Src\Main\Auth\Authorization;

use Src\Main\Database\Eloquent\Model;

interface IAuthorize
{
    public function can(array $abilities, Model $model): bool;
    public function canAny(array $abilities, Model $model): bool;
}
