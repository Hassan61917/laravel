<?php

namespace Src\Main\Cookie;

use Illuminate\Support\Arr;
use Src\Main\Support\Traits\InteractsWithTime;
use Src\Symfony\Http\Cookie;

class CookieJar implements IQueueCookieFactory
{
    use InteractsWithTime;

    protected string $path = "/";
    protected bool $secure = false;
    protected string $sameSite = "lax";
    protected ?string $domain = null;
    protected array $queued = [];
    public function __construct(
        protected array $config = []
    ) {
        $this->path = $config["path"];
        $this->domain = $config["domain"];
        $this->secure = $config["secure"];
        $this->sameSite = $config["same_site"] ?? null;
    }
    public function make(string $name, ?string $value, int $minutes = 0, ?string $path = null, ?string $domain = null, bool $secure = false, bool $httpOnly = true, bool $raw = false, bool $sameSite = null): Cookie
    {
        [$path, $domain, $secure, $sameSite] = $this->getPathAndDomain($path, $domain, $secure, $sameSite);

        $time = $minutes == 0 ? 0 : $this->availableAt($minutes * 60);

        return new Cookie($name, $value, $time, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function forever(string $name, string $value, ?string $path = null, ?string $domain = null, bool $secure = false, ?bool $httpOnly = true, bool $raw = false, bool $sameSite = null): Cookie
    {
        return $this->make($name, $value, 3600 * 30, $path, $domain, $secure, $httpOnly, $raw, $sameSite);
    }
    public function forget(string $name, ?string $path = null, ?string $domain = null): Cookie
    {
        return $this->make($name, null, -1, $path, $domain);
    }
    public function queue(Cookie $cookie): void
    {
        if (! isset($this->queued[$cookie->getName()])) {
            $this->queued[$cookie->getName()] = [];
        }

        $this->queued[$cookie->getName()][$cookie->getPath()] = $cookie;
    }
    public function enqueue(string $name, ?string $path = null): void
    {
        if (is_null($path)) {
            unset($this->queued[$name]);

            return;
        }

        unset($this->queued[$name][$path]);

        if (empty($this->queued[$name])) {
            unset($this->queued[$name]);
        }
    }
    public function getQueuedCookies(): array
    {
        return Arr::flatten($this->queued);
    }
    public function queued(string $key, ?string $default = null, string $path = null): ?Cookie
    {
        $queued = Arr::get($this->queued, $key, $default);

        if (is_null($path)) {
            return Arr::last($queued, null, $default);
        }

        return Arr::get($queued, $path, $default);
    }
    public function hasQueued(string $key, ?string $path = null): bool
    {
        return $this->queued($key, null, $path) != null;
    }
    public function expire(string $name, ?string $path = null, ?string $domain = null): void
    {
        $this->queue($this->forget($name, $path, $domain));
    }
    public function flushQueuedCookies(): static
    {
        $this->queued = [];

        return $this;
    }
    protected function getPathAndDomain(?string $path, ?string $domain, bool $secure = false, ?string $sameSite = null): array
    {
        return [$path ?: $this->path, $domain ?: $this->domain, $secure, $sameSite ?: $this->sameSite];
    }
}
