<?php

namespace Src\Main\Cookie;

class CookieValuePrefix
{
    public static function create(string $cookieName, string $key): string
    {
        return hash_hmac('sha1', $cookieName . 'v2', $key) . '|';
    }
    public static function remove(string $cookieValue): string
    {
        return substr($cookieValue, 41);
    }
    public static function validate(string $cookieName, string $cookieValue, array $keys): ?string
    {
        foreach ($keys as $key) {
            $hasValidPrefix = str_starts_with($cookieValue, static::create($cookieName, $key));

            if ($hasValidPrefix) {
                return static::remove($cookieValue);
            }
        }
        return null;
    }
}
