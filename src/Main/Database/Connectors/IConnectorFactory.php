<?php

namespace Src\Main\Database\Connectors;

interface IConnectorFactory
{
    public function make(string $name): IConnector;
}
