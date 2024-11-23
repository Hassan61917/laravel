<?php

namespace Src\Main\Auth\Authentication;

interface IGuard
{
    public function check(): bool;
    public function guest(): bool;
    public function user(): ?IAuth;
    public function id(): ?string;
    public function validate(array $credentials = []): bool;
    public function hasUser(): bool;
    public function setUser(IAuth $user);
}
