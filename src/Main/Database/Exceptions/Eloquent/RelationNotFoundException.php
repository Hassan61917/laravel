<?php

namespace Src\Main\Database\Exceptions\Eloquent;

use Src\Main\Database\Eloquent\Model;

class RelationNotFoundException extends \RuntimeException
{
    public string $model;
    public string $relation;
    public static function make(Model $model, string $relation, ?string $type = null): static
    {
        $class = get_class($model);

        $instance = new static(
            is_null($type)
                ? "Call to undefined relationship {$relation} on model {$class}."
                : "Call to undefined relationship [{$relation}] on model [{$class}] of type [{$type}].",
        );

        $instance->model = $class;
        $instance->relation = $relation;

        return $instance;
    }
}
