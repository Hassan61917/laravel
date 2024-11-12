<?php

namespace Src\Main\Session;

use Src\Main\Session\Handlers\ISessionHandlerFactory;
use Src\Main\Session\Handlers\SessionHandlerFactory;
use Src\Main\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "session" => [SessionManager::class],
            "session.store" => [ISessionStore::class, SessionStore::class],
        ];
    }
    public function register(): void
    {
        $this->registerSessionManager();
        $this->registerSessionDriver();
    }
    protected function registerSessionManager(): void
    {
        $this->app->singleton(ISessionHandlerFactory::class, SessionHandlerFactory::class);

        $this->app->singleton("session", function ($app) {
            return new SessionManager($app, $app[ISessionHandlerFactory::class]);
        });
    }
    protected function registerSessionDriver(): void
    {
        $this->app->singleton('session.store', function ($app) {
            return $app->make('session')->getDriver();
        });
    }
}
