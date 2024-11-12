<?php

namespace Src\Main\Session\Middlewares;

use Carbon\Carbon;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Routing\Route\Route;
use Src\Main\Session\SessionManager;
use Src\Main\Session\SessionStore;
use Src\Symfony\Http\Cookie;

class StartSession extends Middleware
{
    protected array $config = [];
    public function __construct(
        protected SessionManager $manager,
    ) {
        $this->config = $this->manager->getConfig();
    }
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        if (! $this->sessionConfigured()) {
            return $this->handleNext($request, ...$args);
        }

        $session = $this->getSession($request);

        return $this->handleStatefulRequest($request, $session);
    }
    protected function sessionConfigured(): bool
    {
        return $this->manager->getDefaultDriver() != null;
    }
    protected function getSession(Request $request): SessionStore
    {
        $session = $this->getSessionDriver();

        $id = $request->getCookies()->get($session->getName());

        $session->setId($id);

        return $session;
    }
    protected function getSessionDriver(): SessionStore
    {
        return $this->manager->getDriver();
    }
    protected function handleStatefulRequest(Request $request, SessionStore $session): Response
    {
        $request->setLaravelSession(
            $this->startSession($request, $session)
        );

        $this->collectGarbage($session);

        $response = $this->handleNext($request);

        $this->storeCurrentUrl($request, $session);

        $this->addCookieToResponse($response, $session);

        $this->saveSession();

        return $response;
    }
    protected function startSession(Request $request, SessionStore $session): SessionStore
    {
        $session->setRequestOnHandler($request);

        $session->start();

        return $session;
    }
    protected function collectGarbage(SessionStore $session): void
    {
        if ($this->configHitsLottery()) {
            $session->getHandler()->gc($this->getSessionLifetimeInSeconds());
        }
    }
    protected function configHitsLottery(): bool
    {
        return random_int(1, $this->config['lottery'][1]) <= $this->config['lottery'][0];
    }
    protected function getSessionLifetimeInSeconds(): int
    {
        return ($this->config['lifetime'] ?? 0) * 60;
    }
    protected function storeCurrentUrl(Request $request, SessionStore $session): void
    {
        if (
            $request->isMethod('GET') &&
            $request->route() instanceof Route &&
            ! $request->ajax()
        ) {
            $session->setPreviousUrl($request->fullUrl());
        }
    }
    protected function addCookieToResponse(Response $response, SessionStore $session): void
    {
        $config = $this->config;

        $cookie = new Cookie(
            $session->getName(),
            $session->getId(),
            $this->getCookieExpirationDate(),
            $config['path'],
            $config['domain'],
            $config['secure'] ?? false,
            $config['http_only'] ?? true,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );

        $response->getHeaders()->setCookie($cookie);
    }
    protected function getCookieExpirationDate(): int
    {
        return $this->config['expire_on_close']
            ? 0
            : Carbon::instance(Carbon::now()->addMinutes((int) $this->config['lifetime']))->getTimestamp();
    }
    protected function saveSession(): void
    {
        $this->manager->getDriver()->save();
    }
}
