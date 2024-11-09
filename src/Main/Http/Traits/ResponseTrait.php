<?php

namespace Src\Main\Http\Traits;

use Src\Symfony\Http\Cookie;
use Throwable;

trait ResponseTrait
{
    public string $original;
    public ?Throwable $exception = null;
    public function status(): int
    {
        return $this->getStatusCode();
    }
    public function statusText(): string
    {
        return $this->statusText;
    }
    public function content(): string
    {
        return $this->getContent();
    }
    public function header(string $key, array|string $values, bool $replace = true): static
    {
        $this->headers->set($key, $values, $replace);

        return $this;
    }
    public function withHeaders(array $headers): static
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }
    public function cookie(Cookie $cookie): static
    {
        $this->headers->setCookie($cookie);

        return $this;
    }
}
