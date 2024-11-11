<?php

namespace Src\Main\Encryption;

interface IEncryptor
{
    public function encrypt(mixed $value, bool $serialize = true): string;
    public function decrypt(string $payload, bool $unserialize = true): mixed;
    public function getKey(): string;
}
