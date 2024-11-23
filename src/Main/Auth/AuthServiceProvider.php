<?php

namespace Src\Main\Auth;

use Src\Main\Auth\Authentication\Guards\GuardFactory;
use Src\Main\Auth\Authentication\Guards\IGuardFactory;
use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Auth\Authentication\IGuard;
use Src\Main\Auth\Authorization\Gate;
use Src\Main\Auth\Authorization\IGate;
use Src\Main\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "auth" => [AuthManager::class],
            "auth.driver" => [IGuard::class],
            "gate" => [IGate::class]
        ];
    }

    public function register(): void
    {
        $this->registerAuthenticator();
        $this->registerUserResolver();
        $this->registerAuthorization();
    }
    protected function registerAuthenticator(): void
    {
        $this->app->singleton(IGuardFactory::class, GuardFactory::class);

        $this->app->singleton(
            'auth',
            fn($app) => new AuthManager($app, $app[IGuardFactory::class])
        );

        $this->app->singleton('auth.driver', fn($app) => $app['auth']->getDriver());
    }
    protected function registerUserResolver(): void
    {
        $this->app->bind(IAuth::class, fn($app) => $app['auth.driver']->user());
    }
    protected function registerAuthorization(): void
    {
        $this->app->singleton("gate", fn($app) => new Gate($app));
    }
}
