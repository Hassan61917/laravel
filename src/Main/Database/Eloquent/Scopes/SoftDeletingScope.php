<?php

namespace Src\Main\Database\Eloquent\Scopes;

use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;

class SoftDeletingScope implements IScope
{
    public function apply(EloquentBuilder $builder, Model $model): void {}
}
