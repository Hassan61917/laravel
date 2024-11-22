<?php

namespace Src\Main\Database\Eloquent\Scopes;

use Closure;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;

class ClosureScope implements IScope
{
    public function __construct(
        protected Closure $closure
    ) {}

    public function apply(EloquentBuilder $builder, Model $model): void
    {
        call_user_func($this->closure, $builder, $model);
    }
}
