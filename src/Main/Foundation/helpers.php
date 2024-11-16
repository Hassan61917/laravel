<?php

use Src\Main\Container\Container;
use Src\Main\Http\Response;
use Src\Main\Http\Redirect\RedirectResponse;
use Src\Main\Http\UrlGenerator;
use Src\Main\Support\Env;
use Src\Main\View\View;
use Src\Symfony\Http\Cookie;

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
if (!function_exists('response')) {
    function response(string $content = "", int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
}
if (!function_exists('encrypt')) {
    function encrypt(mixed $value, bool $serialize = true): string
    {
        return app('encryptor')->encrypt($value, $serialize);
    }
}
if (!function_exists('decrypt')) {
    function decrypt(string $value, bool $unserialize = true): mixed
    {
        return app('encryptor')->decrypt($value, $unserialize);
    }
}
if (!function_exists('bcrypt')) {
    function bcrypt(string $value, array $options = []): string
    {
        return app('hash')->getDriver()->make($value, $options);
    }
}
if (!function_exists('cookie')) {
    function cookie(string $name, ?string $value, int $minutes = 0): Cookie
    {
        return app("cookie")->make($name, $value, $minutes);
    }
}
if (!function_exists('session')) {
    function session(string $key, ?string $value = null): mixed
    {
        if ($value) {
            app('session')->put($key, $value);
            return null;
        }

        return app('session')->get($key);
    }
}
if (!function_exists('view')) {
    function view(string $view, array $data = []): View
    {
        return app("view")->make($view, $data);
    }
}
if (! function_exists('translate')) {
    function translate(string $key, ?string $language = null): string
    {
        $language = $language ?: env("APP_LANGUAGE");

        return app('translator')->get($key, $language);
    }
}
if (!function_exists('url')) {
    function url(): UrlGenerator
    {
        return app("url");
    }
}
if (! function_exists('route')) {
    function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        return app('url')->route($name, $parameters, $absolute);
    }
}
if (! function_exists('redirect')) {
    function redirect(string $to, int $status = 302, array $headers = [], bool $secure = false): RedirectResponse
    {
        return app('redirect')->to($to, $status, $headers, $secure);
    }
}
