<?php

namespace Src\Main\Session\Handlers;

use Src\Main\Cookie\CookieJar;
use Src\Main\Http\Request;
use Src\Main\Support\Traits\InteractsWithTime;
use Src\Symfony\Http\Cookie;

class CookieSessionHandler implements \SessionHandlerInterface
{
    use InteractsWithTime;
    protected Request $request;
    public function __construct(
        protected CookieJar $cookie,
        protected int $minutes,
        protected bool $expireOnClose = false
    ) {}
    public function open(string $path, string $name): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }
    public function read(string $id): string
    {
        $value = $this->request->getCookies()->get($id) ?: '';

        if (
            ! is_null($decoded = json_decode($value, true)) && is_array($decoded) &&
            isset($decoded['expires']) && $this->currentTime() <= $decoded['expires']
        ) {
            return $decoded['data'];
        }

        return '';
    }
    public function write(string $id, string $data): bool
    {
        $cookie = new Cookie($id, $data, $this->availableAt($this->minutes * 60));

        $this->cookie->queue($cookie);

        return true;
    }
    public function destroy($id): bool
    {
        $this->cookie->queue($this->cookie->forget($id));

        return true;
    }
    public function gc(int $max_lifetime): int
    {
        return 0;
    }
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
