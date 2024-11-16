<?php

namespace Src\Main\Http;

use Illuminate\Support\Arr;
use Src\Main\Routing\Route\Route;

class RouteUrlGenerator
{
    public array $defaultParameters = [];
    public array $dontEncode = [
        '%2F' => '/',
        '%40' => '@',
        '%3A' => ':',
        '%3B' => ';',
        '%2C' => ',',
        '%3D' => '=',
        '%2B' => '+',
        '%21' => '!',
        '%2A' => '*',
        '%7C' => '|',
        '%3F' => '?',
        '%26' => '&',
        '%23' => '#',
        '%25' => '%',
    ];
    public function __construct(
        protected UrlGenerator $urlGenerator,
        protected Request $request
    ) {}
    public function defaults(array $defaults): void
    {
        $this->defaultParameters = array_merge(
            $this->defaultParameters,
            $defaults
        );
    }
    public function to(Route $route, array $parameters = [], bool $absolute = false): string
    {
        $domain = $this->getRouteDomain($route, $parameters);

        $uri = $this->addQueryString($this->urlGenerator->format(
            $this->replaceRootParameters($route, $domain, $parameters),
            $this->replaceRouteParameters($route->getUri(), $parameters),
        ), $parameters);

        if (preg_match_all('/{(.*?)}/', $uri, $matchedMissingParameters)) {
            throw UrlGenerationException::forMissingParameters($route, $matchedMissingParameters[1]);
        }

        $uri = strtr(rawurlencode($uri), $this->dontEncode);

        if (! $absolute) {
            $uri = preg_replace('#^(//|[^/?])+#', '', $uri);

            if ($base = $this->request->getPathInfo()) {
                $uri = preg_replace('#^' . $base . '#i', '', $uri);
            }

            return '/' . ltrim($uri, '/');
        }

        return $uri;
    }
    protected function getRouteDomain(Route $route, array &$parameters): ?string
    {
        return $route->getDomain() ? $this->formatDomain($route, $parameters) : null;
    }
    protected function formatDomain(Route $route, array &$parameters): string
    {
        return $this->addPortToDomain(
            $this->getRouteScheme() . $route->getDomain()
        );
    }
    protected function getRouteScheme(): ?string
    {
        return $this->urlGenerator->formatScheme();
    }
    protected function addPortToDomain(string $domain): string
    {
        $secure = $this->request->isSecure();

        $port = $this->request->getPort();

        return ($secure && $port === 443) || (! $secure && $port === 80)
            ? $domain : $domain . ':' . $port;
    }
    protected function replaceRootParameters(Route $route, string $domain, array &$parameters): string
    {
        $scheme = $this->getRouteScheme($route);

        return $this->replaceRouteParameters(
            $this->urlGenerator->formatRoot($scheme, $domain),
            $parameters
        );
    }
    protected function replaceRouteParameters(string $path, array &$parameters): string
    {
        $path = $this->replaceNamedParameters($path, $parameters);

        $path = preg_replace_callback('/\{.*?}/', function ($match) use (&$parameters) {
            // Reset only the numeric keys...
            $parameters = array_merge($parameters);

            return (! isset($parameters[0]) && ! str_ends_with($match[0], '?}'))
                ? $match[0]
                : Arr::pull($parameters, 0);
        }, $path);

        return trim(preg_replace('/\{.*?\?}/', '', $path), '/');
    }
    protected function replaceNamedParameters(string $path, array &$parameters): string
    {
        return preg_replace_callback('/\{(.*?)(\?)?}/', function ($m) use (&$parameters) {
            if (isset($parameters[$m[1]]) && $parameters[$m[1]] !== '') {
                return Arr::pull($parameters, $m[1]);
            } elseif (isset($this->defaultParameters[$m[1]])) {
                return $this->defaultParameters[$m[1]];
            } elseif (isset($parameters[$m[1]])) {
                Arr::pull($parameters, $m[1]);
            }

            return $m[0];
        }, $path);
    }
    protected function addQueryString(string $uri, array $parameters): ?string
    {
        $fragment = parse_url($uri, PHP_URL_FRAGMENT);

        if ($fragment) {
            $uri = preg_replace('/#.*/', '', $uri);
        }

        $uri .= $this->getRouteQueryString($parameters);

        return is_null($fragment) ? $uri : $uri . "#{$fragment}";
    }
    protected function getRouteQueryString(array $parameters): string
    {
        if (count($parameters) === 0) {
            return '';
        }

        $query = Arr::query(
            $keyed = $this->getStringParameters($parameters)
        );

        if (count($keyed) < count($parameters)) {
            $query .= '&' . implode(
                '&',
                $this->getNumericParameters($parameters)
            );
        }

        $query = trim($query, '&');

        return $query === '' ? '' : "?{$query}";
    }
    protected function getStringParameters(array $parameters): array
    {
        return array_filter($parameters, 'is_string', ARRAY_FILTER_USE_KEY);
    }
    protected function getNumericParameters(array $parameters): array
    {
        return array_filter($parameters, 'is_numeric', ARRAY_FILTER_USE_KEY);
    }
}
