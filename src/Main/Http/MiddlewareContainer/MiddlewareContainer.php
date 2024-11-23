<?php

namespace Src\Main\Http\MiddlewareContainer;

use Src\Main\Auth\Exceptions\AuthenticationException;
use Src\Main\Auth\Middlewares\Authenticate;

class MiddlewareContainer
{
    protected array $global = [];
    protected array $prepends = [];
    protected array $appends = [];
    protected array $removals = [];
    protected array $replacements = [];
    protected array $groups = [];
    protected array $groupPrepends = [];
    protected array $groupAppends = [];
    protected array $groupRemovals = [];
    protected array $groupReplacements = [];
    protected array $pageMiddleware = [];
    protected array $customAliases = [];
    protected array $priority = [];
    public function redirectTo(?string $guests=null, ?string $users = null): static
    {
        $guests = is_string($guests) ? fn() => $guests : $guests;
        $users = is_string($users) ? fn() => $users : $users;

        if ($guests) {
            Authenticate::redirectUsing($guests);
            AuthenticationException::redirectUsing($guests);
        }

        if ($users) {
        }

        return $this;
    }
    public function redirectGuestsTo(string $redirect): static
    {
        return $this->redirectTo($redirect);
    }
    public function redirectUsersTo(string $redirect): static
    {
        return $this->redirectTo(null, $redirect);
    }
    public function prepend(string ...$middlewares): static
    {
        array_push($this->prepends, ...$middlewares);

        return $this;
    }
    public function append(string ...$middlewares): static
    {
        array_push($this->appends, ...$middlewares);

        return $this;
    }
    public function remove(string ...$middlewares): static
    {
        array_push($this->removals, ...$middlewares);

        return $this;
    }
    public function replace(string $search, string $replace): static
    {
        $this->replacements[$search] = $replace;

        return $this;
    }
    public function use(array $middleware): static
    {
        $this->global = $middleware;

        return $this;
    }
    public function group(string $group, array $middleware): static
    {
        $this->groups[$group] = $middleware;

        return $this;
    }
    public function prependToGroup(string $group, string ...$middlewares): static
    {
        array_push($this->groupPrepends[$group], ...$middlewares);

        return $this;
    }
    public function appendToGroup(string $group, string ...$middlewares): static
    {
        array_push($this->groupAppends[$group], ...$middlewares);

        return $this;
    }
    public function removeFromGroup(string $group, string ...$middlewares): static
    {
        array_push($this->groupRemovals[$group], ...$middlewares);

        return $this;
    }
    public function replaceInGroup(string $group, string $search, string $replace): static
    {
        $this->groupReplacements[$group][$search] = $replace;

        return $this;
    }
    public function web(array $append = [], array $prepend = [], array $remove = [], array $replace = []): static
    {
        return $this->modifyGroup('web', $append, $prepend, $remove, $replace);
    }
    public function api(array $append = [], array $prepend = [], array $remove = [], array $replace = []): static
    {
        return $this->modifyGroup('api', $append, $prepend, $remove, $replace);
    }
    public function pages(array $middleware): static
    {
        $this->pageMiddleware = $middleware;

        return $this;
    }
    public function alias(array $aliases): static
    {
        $this->customAliases = $aliases;

        return $this;
    }
    public function priority(array $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
    public function getGlobalMiddlewares(): array
    {
        $globalMiddleware = require "globalMiddlewares.php";

        $middlewares = $this->global ?: array_values(array_filter($globalMiddleware));

        $middlewares = array_map(
            fn($middleware) => $this->replacements[$middleware] ?? $middleware,
            $middlewares
        );

        $middlewares = array_merge($this->prepends, $middlewares, $this->appends);

        return array_values(array_filter(
            array_diff(array_unique($middlewares), $this->removals)
        ));
    }
    public function getMiddlewareGroups(): array
    {
        $middlewares = require "groupMiddlewares.php";

        $middlewares = array_merge($middlewares, $this->groups);

        foreach ($middlewares as $group => $groupedMiddleware) {
            foreach ($groupedMiddleware as $index => $groupMiddleware) {
                if (isset($this->groupReplacements[$group][$groupMiddleware])) {
                    $middlewares[$group][$index] = $this->groupReplacements[$group][$groupMiddleware];
                }
            }
        }
        foreach ($this->groupRemovals as $group => $removals) {
            $middlewares[$group] = array_values(array_filter(
                array_diff($middlewares[$group] ?? [], $removals)
            ));
        }

        foreach ($this->groupPrepends as $group => $prepends) {
            $middlewares[$group] = array_values(array_filter(
                array_unique(array_merge($prepends, $middlewares[$group] ?? []))
            ));
        }

        foreach ($this->groupAppends as $group => $appends) {
            $middlewares[$group] = array_values(array_filter(
                array_unique(array_merge($middlewares[$group] ?? [], $appends))
            ));
        }

        return $middlewares;
    }
    public function getPageMiddleware(): array
    {
        return $this->pageMiddleware;
    }
    public function getMiddlewareAliases(): array
    {
        return array_merge($this->defaultAliases(), $this->customAliases);
    }
    public function getMiddlewarePriority(): array
    {
        return $this->priority;
    }
    protected function modifyGroup(string $group, array $append, array $prepend, array $remove, array $replace): static
    {
        if (!empty($append)) {
            $this->appendToGroup($group, ...$append);
        }
        if (!empty($prepend)) {
            $this->prependToGroup($group, ...$prepend);
        }
        if (!empty($remove)) {
            $this->removeFromGroup($group, ...$remove);
        }
        if (!empty($replace)) {
            foreach ($replace as $search => $value) {
                $this->replaceInGroup($group, $search, $value);
            }
        }

        return $this;
    }
    protected function defaultAliases(): array
    {
        return require "defaultAliases.php";
    }
}
