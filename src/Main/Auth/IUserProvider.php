<?php

namespace Src\Main\Auth;

use Src\Main\Auth\Authentication\IAuth;

interface IUserProvider
{
    public function getById(string $id): ?IAuth;
    public function getByToken(string $id, string $token): ?IAuth;
    public function getByCredentials(array $credentials): ?IAuth;
    public function updateRememberToken(IAuth $user, string $token): void;
    public function validateCredentials(IAuth $user, array $credentials): bool;
    public function rehashPasswordIfRequired(IAuth $user, array $credentials, bool $force = false): void;
}
