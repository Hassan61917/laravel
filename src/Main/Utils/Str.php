<?php

namespace Src\Main\Utils;

class Str
{
    protected static array $studlyCache = [];
    protected static array $snakeCache = [];
    protected static array $camelCache = [];
    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $words = explode(' ', static::replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn($word) => static::ucfirst($word), $words);

        return static::$studlyCache[$key] = implode($studlyWords);
    }
    public static function replace(string|array $search, string|array $replace, string|array $subject, bool $caseSensitive = true): array|string
    {
        return $caseSensitive
            ? str_replace($search, $replace, $subject)
            : str_ireplace($search, $replace, $subject);
    }
    public static function ucfirst($string): string
    {
        return static::upper(static::substr($string, 0, 1)) . static::substr($string, 1);
    }
    public static function upper(string $value): string
    {
        return strtoupper($value);
    }
    public static function substr(string $string, int $start, int $length = null): string
    {
        return substr($string, $start, $length);
    }
    public static function lower(string $value): string
    {
        return strtolower($value);
    }
    public static function snake(string $value, string $delimiter = "_"): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        $value = preg_replace('/\s+/u', '', ucwords($value));

        $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', "$1" . $delimiter, $value));

        return static::$snakeCache[$key][$delimiter] = $value;
    }
    public static function after(string $subject, string $search): string
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }
    public static function random(int $length = 16): string
    {
        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $result[] = chr(random_int(97, 122));
        }
        return implode('', $result);
    }
    public static function parseCallback(string $callback, string $default = null): array
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }
    public static function contains(string $haystack, string ...$needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function uuid(): string
    {
        return static::random(32);
    }
    public static function replaceFirst(string $search, string $replace, string $subject): string
    {

        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }

    public static function afterLast(string $subject, string $search): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position === false) {
            return $subject;
        }

        return substr($subject, $position + strlen($search));
    }
    public static function camel(string $value): string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }
    public static function pluralStudly(string $value, int $count = 2): string
    {
        $map = ["y" => "ies", "s", "es"];
        $lastChar = substr($value, strlen($value) - 1, 1);
        if (array_key_exists($lastChar, $map)) {
            return substr($value, 0, -1) . $map[$lastChar];
        }
        return $value . "s";
    }
    public static function singular(string $value): string
    {
        if (str_ends_with($value, "ies")) {
            $value = substr($value, 0, -3);
        }
        if (str_ends_with($value, "es")) {
            $value = substr($value, 0, -2);
        }
        if (str_ends_with($value, "s")) {
            $value = substr($value, 0, -1);
        }
        return $value;
    }
    public static function slug(string $title, string $separator = '-', array $dictionary = ['@' => 'at']): string
    {
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        foreach ($dictionary as $key => $value) {
            $dictionary[$key] = $separator . $value . $separator;
        }

        $title = str_replace(array_keys($dictionary), array_values($dictionary), $title);

        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', static::lower($title));

        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }
    public static function finish(string $value, string $cap): string
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }
    public static function start(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }
    public static function replaceLast(string $search, string $replace, string $subject): string
    {
        if (empty($search)) {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
    public static function kebab(string $value): string
    {
        return static::snake($value, '-');
    }
    public static function before(string $subject, string $search): string
    {
        if (empty($search)) {
            return $subject;
        }

        $result = strstr($subject, $search, true);

        return $result === false ? $subject : $result;
    }
}
