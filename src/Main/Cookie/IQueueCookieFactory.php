<?php

namespace Src\Main\Cookie;

use Src\Symfony\Http\Cookie;

interface IQueueCookieFactory extends ICookieFactory
{
    public function queue(Cookie $cookie): void;
    public function enqueue(string $name, ?string $path = null): void;
    public function hasQueued(string $key, ?string $path = null): bool;
    public function getQueuedCookies(): array;
}
