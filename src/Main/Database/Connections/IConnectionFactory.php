<?php

namespace Src\Main\Database\Connections;

interface IConnectionFactory
{
    public function make(string $name, array $config = []): Connection;
}
