<?php

namespace Src\Main\Http;

use Closure;
use RuntimeException;
use Src\Main\Http\Traits\InteractsWithContentTypes;
use Src\Main\Http\Traits\InteractsWithInput;
use Src\Main\Routing\Route\Route;
use Src\Main\Session\ISessionStore;
use Src\Main\Session\SymfonySessionDecorator;
use Src\Symfony\Http\IRequestInput;
use Src\Symfony\Http\ISession;
use Src\Symfony\Http\Request as BaseRequest;
use Src\Symfony\Http\RequestInput;

class Request extends BaseRequest
{
    use InteractsWithInput,
        InteractsWithContentTypes;

    protected ?IRequestInput $json = null;
    protected Closure $routeResolver;

    public static function capture(): static
    {
        static::enableHttpMethodOverride();

        return static::createFromGlobals();
    }
    public function setRouteResolver(Closure $routeResolver): void
    {
        $this->routeResolver = $routeResolver;
    }
    public function getRouteResolver(): Closure
    {
        return $this->routeResolver;
    }
    public function path(): string
    {
        $pattern = trim($this->getPathInfo(), '/');

        return $pattern === '' ? '/' : $pattern;
    }
    public function segment($index, $default = null): ?string
    {
        return $this->segments()[$index - 1] ?? $default;
    }
    public function segments(): array
    {
        $segments = explode('/', $this->decodedPath());
        $segments = array_filter($segments, fn($s) => $s !== "");
        return array_values($segments);
    }
    public function decodedPath(): string
    {
        return rawurldecode($this->path());
    }
    public function route(): ?Route
    {
        return call_user_func($this->getRouteResolver());
    }
    public function getRouteParam(string $key, mixed $default = null): mixed
    {
        $route = call_user_func($this->getRouteResolver());

        if ($route) {
            return $route->getParameter($key, $default);
        }

        return $route;
    }
    public function routeIs(array ...$names): bool
    {
        return $this->route() && $this->route()->named(...$names);
    }
    public function ajax(): bool
    {
        return $this->isXmlHttpRequest();
    }
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }
    public function url(): string
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }
    public function fullUrl(): string
    {
        $query = $this->getQueryString();

        $question = $this->getPathInfo() === '/' ? '/?' : '?';

        return $query ? $this->url() . $question . $query : $this->url();
    }
    public function json(string $key = null)
    {
        if (! isset($this->json)) {
            $this->json = new RequestInput((array) json_decode($this->getContent(), true));
        }

        if (is_null($key)) {
            return $this->json;
        }

        return data_get($this->json->all(), $key);
    }
    public function setLaravelSession(ISessionStore $session): void
    {
        $this->session = new SymfonySessionDecorator($session);
    }
    public function session(): ?ISession
    {
        if (! $this->hasSession()) {
            throw new RuntimeException('Session store not set on request.');
        }

        return $this->session;
    }
    protected function getInputSource()
    {
        if ($this->isJson()) {
            return $this->json();
        }

        return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
    }
}
