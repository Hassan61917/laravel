<?php

namespace Src\Main\Database\Connectors;

use PDO;

abstract class PdoConnector implements IConnector
{
    protected array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
    protected function createConnection(string $dsn, array $config, array $options): PDO
    {
        [$username, $password] = [
            $config['username'] ?? null,
            $config['password'] ?? null,
        ];

        return $this->createPdoConnection($dsn, $username, $password, $options);
    }
    protected function createPdoConnection(string $dsn, string $username, string $password, array $options): PDO
    {
        return new PDO($dsn, $username, $password, $options);
    }
    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }
    public function getOptions(): array
    {
        $options = $config['options'] ?? [];

        return array_diff_key($this->options, $options) + $options;
    }
}
