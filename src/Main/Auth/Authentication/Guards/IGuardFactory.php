<?php

namespace Src\Main\Auth\Authentication\Guards;

use Src\Main\Auth\Authentication\IGuard;

interface IGuardFactory
{
    public function make(string $name, array $config): IGuard;
}
