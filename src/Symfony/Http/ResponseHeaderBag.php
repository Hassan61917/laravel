<?php

namespace Src\Symfony\Http;

use Illuminate\Support\Arr;

class ResponseHeaderBag extends HeaderBag
{
    protected array $computedCacheControl = [];
    protected array $cookies = [];
    protected array $headerNames = [];
    public function __construct(
        protected array $headers
    ) {
        parent::__construct($headers);
        $this->init();
    }
    public function allHeaders(): array
    {
        $headers = [];

        foreach ($this->all() as $name => $value) {
            $headers[$this->headerNames[$name] ?? $name] = $value;
        }

        return $headers;
    }
    public function headersWithoutCookies(): array
    {
        $headers = $this->allHeaders();

        if ($this->hasHeaderName()) {
            unset($headers[$this->headerNames['set-cookie']]);
        }

        return $headers;
    }
    public function replace(array $headers = []): void
    {
        $this->headerNames = [];

        parent::replace($headers);

        $this->init();
    }
    public function all(?string $key = null): array
    {
        $headers = parent::all();

        $cookies = $this->getCookies();

        if ($key) {
            $key = $this->get($key);

            return $key !== 'set-cookie' ? $headers[$key] ?? [] : array_map('strval', $cookies);
        }

        foreach ($cookies as $cookie) {
            $headers['set-cookie'][] = (string) $cookie;
        }

        return $headers;
    }
    public function set(string $key, string|array|null $values, bool $replace = true): void
    {
        $uniqueKey = $this->getKey($key);

        if ($uniqueKey === 'set-cookie') {
            if ($replace) {
                $this->cookies = [];
            }

            foreach ((array) $values as $cookie) {
                $this->setCookie(Cookie::fromString($cookie));
            }

            $this->addHeaderName($uniqueKey, $key);

            return;
        }

        $this->headerNames[$uniqueKey] = $key;

        parent::set($key, $values, $replace);

        $computed = $this->computeCacheControlValue();

        if (in_array($uniqueKey, ['cache-control', 'etag', 'last-modified', 'expires'], true) && $computed != "") {
            $this->headers['cache-control'] = [$computed];
            $this->headerNames['cache-control'] = 'Cache-Control';
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
    }
    public function remove(string $key): void
    {
        $uniqueKey = $this->getKey($key);

        unset($this->headerNames[$uniqueKey]);

        if ($uniqueKey === 'set-cookie') {
            $this->cookies = [];

            return;
        }

        parent::remove($key);

        if ($uniqueKey === 'cache-control') {
            $this->computedCacheControl = [];
        }

        if ($uniqueKey === "date") {
            $this->initDate();
        }
    }
    public function hasCacheControlDirective(string $key): bool
    {
        return array_key_exists($key, $this->computedCacheControl);
    }
    public function getCacheControlDirective(string $key): ?string
    {
        return $this->computedCacheControl[$key] ?? null;
    }
    public function setCookie(Cookie $cookie): void
    {
        $this->addCookie($cookie);
        $this->addHeaderName('set-cookie', 'Set-Cookie');
    }
    public function getCookies(string $format = "flat"): array
    {
        if ($format != "flat") {
            return $this->cookies;
        }

        return Arr::flatten($this->cookies);
    }
    public function removeCookie(string $name, ?string $path = '/', ?string $domain = null): void
    {
        $path ??= '/';

        unset($this->cookies[$domain][$path][$name]);

        if (empty($this->cookies[$domain][$path])) {
            unset($this->cookies[$domain][$path]);

            if (empty($this->cookies[$domain])) {
                unset($this->cookies[$domain]);
            }
        }

        if (empty($this->cookies)) {
            unset($this->headerNames['set-cookie']);
        }
    }
    protected function computeCacheControlValue(): string
    {
        if (!$this->cacheControl) {
            if ($this->has('Last-Modified') || $this->has('Expires')) {
                return 'private, must-revalidate';
            }
            return 'no-cache, private';
        }

        $header = $this->getCacheControlHeader();

        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }

        if (!isset($this->cacheControl['s-maxage'])) {
            return $header . ', private';
        }

        return $header;
    }
    protected function init(): void
    {
        if (!$this->hasHeader("cache-control")) {
            $this->set("Cache-Control", "");
        }

        if (!$this->hasHeader("date")) {
            $this->initDate();
        }
    }
    protected function initDate(): void
    {
        $this->set('Date', gmdate('D, d M Y H:i:s') . ' GMT');
    }
    protected function hasHeaderName(): bool
    {
        return isset($this->headerNames['set-cookie']);
    }
    protected function addCookie(Cookie $cookie): void
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
    }
    protected function addHeaderName(string $key, string $value): void
    {
        $this->headerNames[$key] = $value;
    }
}
