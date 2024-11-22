<?php

namespace Src\Main\Database\Eloquent\Factories;

trait HasFactory
{
    public static function factory(int $count = 1, array $state = []): Factory
    {
        $factory = Factory::factoryForModel(get_called_class());

        return $factory
            ->count($count)
            ->state($state);
    }
}
