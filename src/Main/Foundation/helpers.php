<?php

use Src\Main\Container\Container;
use Src\Main\Support\Env;

if (!function_exists('app')) {
    function app(?string $abstract = null): object
    {
        if (is_null($abstract)) {
            return Container::getInstance();
        }
        return Container::getInstance()->make($abstract);
    }
}
if (!function_exists('join_paths')) {
    function join_paths(string $basePath, string ...$paths): string
    {
        foreach ($paths as $index => $path) {
            if (empty($path)) {
                unset($paths[$index]);
            } else {
                $paths[$index] = DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            }
        }
        return $basePath . implode('', $paths);
    }
}
if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}
if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return app()->storagePath($path);
    }
}
if (!function_exists('resource_path')) {
    function resource_path(string $path = ''): string
    {
        return app()->resourcePath($path);
    }
}
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}
