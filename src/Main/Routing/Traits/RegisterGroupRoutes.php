<?php

namespace Src\Main\Routing\Traits;

use Closure;
use Src\Main\Routing\PendingResourceRegistration;
use Src\Main\Routing\ResourceRegistrar;
use Src\Main\Routing\Route\Route;
use Src\Main\Routing\RouteGroup;

trait RegisterGroupRoutes
{
    protected array $groupData = [];
    public function group(array $attributes, Closure $routes): static
    {
        $this->updateGroupData($attributes);

        call_user_func($routes, $this);

        array_pop($this->groupData);

        return $this;
    }
    public function resource(string $name, string $controller, array $options = []): PendingResourceRegistration
    {
        $registrar = new ResourceRegistrar($this);

        return new PendingResourceRegistration(
            $registrar,
            $name,
            $controller,
            $options
        );
    }
    public function apiResource(string $name, string $controller, array $options = []): PendingResourceRegistration
    {
        $only = ['index', 'show', 'store', 'update', 'destroy'];

        if (isset($options['except'])) {
            $only = array_diff($only, $options['except']);
        }

        $options =  array_merge(['only' => $only], $options);

        return $this->resource($name, $controller, $options);
    }
    protected function prefix(string $uri): string
    {
        $uri = trim($uri, '/');

        $groupPrefix = trim($this->getLastGroupPrefix(), '/');

        return trim($groupPrefix . '/' . $uri, '/') ?: '/';
    }
    protected function getLastGroupPrefix(): string
    {
        if ($this->hasGroupData()) {
            $last = end($this->groupData);

            return $last['prefix'] ?? '';
        }

        return '';
    }
    protected function hasGroupData(): bool
    {
        return count($this->groupData);
    }
    protected function mergeWithRoute(Route $route): void
    {
        $route->setData($this->mergeWithLast($route->getData()));
    }
    protected function updateGroupData(array $attributes): void
    {
        if ($this->hasGroupData()) {
            $attributes = $this->mergeWithLast($attributes);
        }

        $this->groupData[] = $attributes;
    }
    protected function mergeWithLast(array $attributes): array
    {
        return RouteGroup::merge(end($this->groupData), $attributes);
    }
}
