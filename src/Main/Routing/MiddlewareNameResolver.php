<?php

namespace Src\Main\Routing;

class MiddlewareNameResolver
{
    public static function resolve(string $name, array $middlewares, array $groups): array
    {
        if (isset($groups[$name])) {
            return static::parseMiddlewareGroup($name, $middlewares, $groups);
        }
        return [self::parseMiddleware($name, $middlewares)];
    }
    private static function parseMiddlewareGroup(string $name, array $middlewares, array $groups): array
    {
        $result = [];

        foreach ($groups[$name] as $middleware) {
            if (isset($groups[$middleware])) {
                $items = static::parseMiddlewareGroup($middleware, $middlewares, $groups);
                array_push($results, ...$items);
            } else {
                $result[] = self::parseMiddleware($middleware, $middlewares);
            }
        }

        return $result;
    }
    private static function parseMiddleware(string $name, array $middlewares): string
    {
        [$name, $parameters] = array_pad(
            explode(':', $name, 2),
            2,
            null
        );

        if (isset($middlewares[$name])) {
            $name = $middlewares[$name];
        }

        if (!is_null($parameters)) {
            $name .= ':' . $parameters;
        }
        return $name;
    }
}
