<?php

namespace Src\Main\Hashing\Drivers;

interface IHashDriverFactory
{
    public function make(string $name, array $config = []): IHashDriver;
}
