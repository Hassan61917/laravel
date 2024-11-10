<?php

namespace Src\Main\Routing;

use Src\Main\Routing\Route\Route;
use Src\Main\Utils\Str;

class ResourceRegistrar
{
    protected static bool $singularParameters = true;
    protected static array $parameterMap = [];
    protected array $defaults = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
    public function __construct(
        protected Router $router
    ) {}
    public function register(string $name, string $controller, array $options = []): void
    {
        $base = $this->getResourceWildcard(last(explode('.', $name)));

        $defaults = $this->defaults;

        $methods = $this->getResourceMethods($defaults, $options);

        foreach ($methods as $method) {
            $method = "addResource" . ucfirst($method);

            if (!method_exists($this, $method)) {
                continue;
            }

            $this->$method($name, $base, $controller, $options);
        }
    }
    protected function getResourceWildcard(string $value): string
    {
        if (isset(static::$parameterMap[$value])) {
            $value = static::$parameterMap[$value];
        }

        if (static::$singularParameters) {
            $value = Str::singular($value);
        }

        return str_replace('-', '_', $value);
    }
    protected function getResourceMethods(array $defaults, array $options): array
    {
        $methods = $defaults;

        if (isset($options['only'])) {
            $methods = array_intersect($methods, $options['only']);
        }

        if (isset($options['except'])) {
            $methods = array_diff($methods, $options['except']);
        }

        return array_values($methods);
    }
    protected function addResourceIndex(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name);

        $data = $this->getResourceData($name, $controller, "index", $options);

        return $this->router->get($uri, $data["controller"], $data);
    }
    protected function addResourceCreate(string $name, string $base, string $controller, array $options): Route
    {
        $method = 'create';

        $uri = $this->getResourceUri($name) . '/' . $method;

        $data = $this->getResourceData($name, $controller, $method, $options);

        return $this->router->get($uri, $data["controller"], $data);
    }
    protected function addResourceStore(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name);

        $data = $this->getResourceData($name, $controller, 'store', $options);

        return $this->router->post($uri, $data["controller"], $data);
    }
    protected function addResourceShow(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . $this->createWildcard($base);

        $data = $this->getResourceData($name, $controller, 'show', $options);

        return $this->router->get($uri, $data["controller"], $data);
    }
    protected function addResourceEdit(string $name, string $base, string $controller, array $options): Route
    {
        $method = 'edit';

        $uri = $this->getResourceUri($name) . $this->createWildcard($base) . $method;

        $data = $this->getResourceData($name, $controller, $method, $options);

        return $this->router->get($uri, $data["controller"], $data);
    }
    protected function addResourceUpdate(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . $this->createWildcard($base);

        $data = $this->getResourceData($name, $controller, 'update', $options);

        return $this->router->put($uri, $data["controller"], $data);
    }
    protected function addResourceDestroy(string $name, string $base, string $controller, array $options): Route
    {
        $uri = $this->getResourceUri($name) . $this->createWildcard($base);

        $data = $this->getResourceData($name, $controller, 'destroy', $options);

        return $this->router->delete($uri, $data["controller"], $data);
    }
    protected function getResourceUri(string $resource): string
    {
        if (! str_contains($resource, '.')) {
            return $resource;
        }

        $segments = explode('.', $resource);

        $uri = $this->getNestedResourceUri($segments);

        return str_replace($this->createWildcard(end($segments)), '', $uri);
    }
    protected function getNestedResourceUri(array $segments): string
    {
        $segments = array_map(fn($s) => $s . $this->createWildcard($s), $segments);

        return implode('/', $segments);
    }
    protected function createWildcard(string $value): string
    {
        $value = $this->getResourceWildcard($value);

        return "/{{$value}}";
    }
    protected function getResourceData(string $resource, string $controller, string $method, array $options): array
    {
        $name = $this->getResourceRouteName($resource, $method, $options);

        $data = ['name' => $name, "controller" => [$controller, $method]];

        if (isset($options['middleware'])) {
            $data['middleware'] = $options['middleware'];
        }

        return $data;
    }
    protected function getResourceRouteName(string $resource, string $method, array $options): string
    {
        if (isset($options['names'][$method])) {
            return $options['names'][$method];
        }

        $name = $resource;

        if (isset($options['name'])) {
            $name = $options['name'];
        }

        $prefix = isset($options['prefix']) ? $options['prefix'] . "." : '';

        return trim("{$prefix}{$name}.{$method}", '.');
    }
}
