<?php

namespace Src\Main\Database\Eloquent\Casts;

use Closure;

class Json
{
    protected static ?Closure $encoder = null;
    protected static ?Closure $decoder = null;
    public static function encode(mixed $value): mixed
    {
        return isset(static::$encoder) ? (static::$encoder)($value) : json_encode($value);
    }
    public static function decode(mixed $value, ?bool $associative = true): mixed
    {
        return isset(static::$decoder)
            ? (static::$decoder)($value, $associative)
            : json_decode($value, $associative);
    }
    public static function encodeUsing(?callable $encoder): void
    {
        static::$encoder = $encoder;
    }
    public static function decodeUsing(?callable $decoder): void
    {
        static::$decoder = $decoder;
    }
}
