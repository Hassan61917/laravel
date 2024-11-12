<?php

namespace Src\Main\Session\Handlers;

use SessionHandlerInterface;

interface ISessionHandlerFactory
{
    public function make(string $name, array $config): SessionHandlerInterface;
}
