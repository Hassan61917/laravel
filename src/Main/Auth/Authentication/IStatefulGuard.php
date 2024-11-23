<?php

namespace Src\Main\Auth\Authentication;

interface IStatefulGuard extends IGuard
{
    public function attempt(array $credentials = [], bool $remember = false): bool;
    public function once(array $credentials = []): bool;
    public function login(IAuth $user, bool $remember = false): void;
    public function loginUsingId(string $id, bool $remember = false): ?IAuth;
    public function onceUsingId(string $id): ?IAuth;
    public function viaRemember(): bool;
    public function logout(): void;
}
