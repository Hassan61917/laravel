<?php

namespace Src\Main\Http\Redirect;

use Src\Main\Http\UrlGenerator;
use Src\Main\Session\ISessionStore;

class Redirector
{
    public function __construct(
        protected UrlGenerator $generator,
        protected ISessionStore $session
    ) {}
    public function to(string $path, int $status = 302, array $headers = [], bool $secure = false): RedirectResponse
    {
        return $this->createRedirect(
            $this->generator->to($path, [], $secure),
            $status,
            $headers
        );
    }
    public function back(int $status = 302, array $headers = [], bool $fallback = false): RedirectResponse
    {
        return $this->createRedirect(
            $this->generator->previous($fallback),
            $status,
            $headers
        );
    }
    public function refresh(int $status = 302, array $headers = []): RedirectResponse
    {
        return $this->to($this->generator->getRequest()->path(), $status, $headers);
    }
    public function guest(string $path, int $status = 302, array $headers = [], bool $secure = false): RedirectResponse
    {
        $request = $this->generator->getRequest();

        $intended = $request->isMethod('GET') && $request->route() && ! $request->expectsJson()
            ? $this->generator->full()
            : $this->generator->previous();

        if ($intended) {
            $this->setIntendedUrl($intended);
        }

        return $this->to($path, $status, $headers, $secure);
    }
    public function away(string $path, int $status = 302, array $headers = []): RedirectResponse
    {
        return $this->createRedirect($path, $status, $headers);
    }
    public function secure(string $path, int $status = 302, array $headers = []): RedirectResponse
    {
        return $this->to($path, $status, $headers, true);
    }
    public function route(string $route, array $parameters = [], int $status = 302, array $headers = []): RedirectResponse
    {
        return $this->to(
            $this->generator->route($route, $parameters),
            $status,
            $headers
        );
    }
    protected function setIntendedUrl(string $url): static
    {
        $this->session->put('url.intended', $url);

        return $this;
    }
    protected function createRedirect(string $path, int $status, array $headers): RedirectResponse
    {
        $response = new RedirectResponse($path, $status, $headers);

        if (isset($this->session)) {
            $response->setSession($this->session);
        }

        $response->setRequest($this->generator->getRequest());

        return $response;
    }
}
