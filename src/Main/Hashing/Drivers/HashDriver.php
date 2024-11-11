<?php

namespace Src\Main\Hashing\Drivers;

abstract class HashDriver implements IHashDriver
{
    public function info(string $hash): array
    {
        return password_get_info($hash);
    }
    public function check(string $value, string $hash, array $options = []): bool
    {
        if (strlen($hash) === 0) {
            return false;
        }

        return password_verify($value, $hash);
    }
}
