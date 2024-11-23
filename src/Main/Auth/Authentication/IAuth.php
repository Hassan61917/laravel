<?php

namespace Src\Main\Auth\Authentication;

interface IAuth
{
    public function getIdName();
    public function getId(): string;
    public function getAuthPasswordName(): string;
    public function getAuthPassword(): string;
    public function getRememberTokenName(): ?string;
    public function setRememberToken(?string $value): static;
    public function getRememberToken(): ?string;
}
