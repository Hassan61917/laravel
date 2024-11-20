<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Database\Schema\Builders\SchemaBuilder;
use Src\Main\Facade\Facade;

class Schema extends Facade
{
    protected static bool $cached = false;
    public static function connection(?string $name = null): SchemaBuilder
    {
        return static::$app['db']->connection($name)->getSchemaBuilder();
    }
    protected static function getAccessor(): string
    {
        return 'db.schema';
    }
}
