<?php

namespace Src\Main\Database;

use Src\Main\Database\Connections\Connection;

interface IConnectionResolver
{
    public function connection(string $name = null): Connection;

    public function getDefaultConnection(): string;

    public function setDefaultConnection(string $name): void;
}
