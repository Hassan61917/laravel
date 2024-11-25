<?php

namespace Src\Main\Queue\Connectors;

interface IConnectorFactory
{
    public function make(string $name): IConnector;
}
