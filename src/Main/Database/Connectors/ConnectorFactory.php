<?php

namespace Src\Main\Database\Connectors;

class ConnectorFactory implements IConnectorFactory
{
  public function make(string $name): IConnector
  {
    return match ($name) {
      "mysql" => new MySqlConnector(),
    };
  }
}
