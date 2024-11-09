<?php

namespace Src\Main\Http;

abstract class Middleware
{
    protected ?Middleware $next = null;
    public function setNext(?Middleware $next): void
    {
        $this->next = $next;
    }
    public function handle(Request $request, string ...$args): Response
    {
        $response = $this->doHandle($request,  ...$args);

        if (!$this->hasNext() || $response) {
            return $response;
        }

        return $this->handleNext($request, ...$args);
    }
    protected function hasNext(): bool
    {
        return $this->next != null;
    }
    protected function handleNext(Request $request, string ...$args): Response
    {
        return $this->next->handle($request, ...$args);
    }
    protected abstract function doHandle(Request $request, string ...$args): ?Response;
}
