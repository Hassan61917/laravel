<?php

namespace Src\Main\Encryption;

interface IStringEncryptor
{
    public function encryptString(string $value): string;
    public function decryptString(string $payload): string;
}
