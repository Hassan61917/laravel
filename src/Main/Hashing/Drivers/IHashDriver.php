<?php

namespace Src\Main\Hashing\Drivers;

interface IHashDriver
{
    public function info(string $hash): array;
    public function make(string $value, array $options = []): string;
    public function check(string $value, string $hash, array $options = []): bool;
    public function needsRehash(string $hash, array $options = []): bool;
}
