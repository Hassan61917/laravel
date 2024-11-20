<?php

namespace Src\Main\Database\Connectors;

use PDO;

interface IConnector
{
    public function connect(array $config): PDO;
}
