<?php

namespace Src\Main\Auth\Traits;

use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Auth\Exceptions\AuthenticationException;
use Src\Main\Auth\IUserProvider;

trait GuardHelper
{
    protected ?IAuth $user = null;
    public function authenticate(): IAuth
    {
        return $this->user() ?? throw new AuthenticationException;
    }
    public function setUser(IAuth $user): static
    {
        $this->user = $user;

        return $this;
    }
    public function hasUser(): bool
    {
        return $this->user != null;
    }
    public function check(): bool
    {
        return $this->user != null;
    }
    public function guest(): bool
    {
        return ! $this->check();
    }
    public function id(): ?string
    {
        return $this->user()?->getId();
    }
    public function forgetUser(): static
    {
        $this->user = null;

        return $this;
    }
    public function getProvider(): IUserProvider
    {
        return $this->provider;
    }
}
