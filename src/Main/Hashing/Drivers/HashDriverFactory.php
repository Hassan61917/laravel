<?php

namespace Src\Main\Hashing\Drivers;

class HashDriverFactory implements IHashDriverFactory
{
    public function make(string $name, array $config = []): IHashDriver
    {
        return match ($name) {
            "bcrypt" => new BcryptDriver($config),
            default => throw new \InvalidArgumentException("driver '{$name}' is not supported"),
        };
    }
}
