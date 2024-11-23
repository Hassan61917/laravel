<?php

namespace Src\Main\Auth\Traits;

trait WithAuth
{
    protected string $authPasswordName = 'password';
    protected ?string $rememberTokenName = 'remember_token';
    public function getIdName(): string
    {
        return $this->getKeyName();
    }
    public function getId(): string
    {
        $id = $this->getIdName();

        return $this->$id;
    }
    public function getAuthPasswordName(): string
    {
        return $this->authPasswordName;
    }
    public function getAuthPassword(): string
    {
        return $this->{$this->getAuthPasswordName()};
    }
    public function getRememberTokenName(): ?string
    {
        return $this->rememberTokenName;
    }
    public function setRememberToken(?string $value): static
    {
        $tokenName = $this->getRememberTokenName();

        if ($tokenName) {
            $this->{$tokenName} = $value;
        }

        return $this;
    }
    public function getRememberToken(): ?string
    {
        return (string) $this->{$this->getRememberTokenName()};
    }
}
