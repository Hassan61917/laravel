<?php

namespace Src\Main\Auth\Authentication\Guards;

use Src\Main\Auth\Authentication\IGuard;
use Src\Main\Auth\CreateUserProvider;
use Src\Main\Auth\IUserProvider;
use Src\Main\Foundation\IApplication;

class GuardFactory implements IGuardFactory
{
    public function __construct(
        protected IApplication $app
    ) {}
    public function make(string $name, array $config): IGuard
    {
        $userProvider = $this->resolveUserProvider($config['provider'] ?? null);

        return match ($name) {
            "web" => $this->createSessionGuard($name, $userProvider)
        };
    }
    protected function createSessionGuard(string $name, IUserProvider $userProvider): SessionGuard
    {
        return new SessionGuard(
            $name,
            $userProvider,
            $this->app["session.store"],
            $this->app["cookie"],
            $this->app["request"]
        );
    }
    protected function resolveUserProvider(?string $provider): IUserProvider
    {
        return (new CreateUserProvider($this->app))->create($provider);
    }
}
