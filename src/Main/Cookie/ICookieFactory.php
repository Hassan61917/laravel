<?php

namespace Src\Main\Cookie;

use Src\Symfony\Http\Cookie;

interface ICookieFactory
{
    public function make(string $name, ?string $value, int $minutes = 0, ?string $path = null, ?string $domain = null, bool $secure = false, bool $httpOnly = true, bool $raw = false, bool $sameSite = null): Cookie;
    public function forever(string $name, string $value, ?string $path = null, ?string $domain = null, bool $secure = false, ?bool $httpOnly = true, bool $raw = false, bool $sameSite = null): Cookie;
    public function forget(string $name, ?string $path = null, ?string $domain = null): Cookie;
}
