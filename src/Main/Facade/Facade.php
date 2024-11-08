<?php

namespace Src\Main\Facade;

use Closure;
use InvalidArgumentException;
use RuntimeException;
use Src\Main\Foundation\IApplication;

abstract class Facade
{
    protected static IApplication $app;
    protected static array $resolvedInstances = [];
    protected static bool $cached = true;
    public static function setApp(IApplication $app): void
    {
        self::$app = $app;
    }
    public static function getApp(): IApplication
    {
        return self::$app;
    }
    public static function getFacadeRoot()
    {
        return static::resolveInstance(static::getAccessor());
    }
    public static function resolved(Closure $callback): void
    {
        $accessor = static::getAccessor();

        if (static::$app->resolved($accessor)) {
            $callback(static::getFacadeRoot(), static::$app);
        }

        static::$app->afterResolving($accessor, fn($service, $app) =>  $callback($service, $app));
    }
    public static function clearInstance(string $name): void
    {
        unset(static::$resolvedInstances[$name]);
    }
    public static function clearInstances(): void
    {
        static::$resolvedInstances = [];
    }
    protected static function getAccessor(): string
    {
        return "";
    }
    protected static function resolveInstance(string $name): ?object
    {
        if (self::hasInstance($name)) {
            return static::$resolvedInstances[$name];
        }
        if (static::$app) {
            $instance = static::$app[$name];
            if (static::$cached) {
                self::setInstance($name, $instance);
            }
            return $instance;
        }
        throw new InvalidArgumentException("app does not set");
    }
    protected static function hasInstance(string $name): bool
    {
        return isset(static::$resolvedInstances[$name]);
    }
    protected static function setInstance(string $name, object $instance): void
    {
        static::$resolvedInstances[$name] = $instance;
    }
    public static function __callStatic(string $method, mixed $args)
    {
        $instance = static::getFacadeRoot();

        if (! $instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
