<?php

namespace Src\Main\Database\Connectors;

class MysqlConnector extends PdoConnector
{
    public function connect(array $config): \PDO
    {
        $dsn = $this->getDsn($config);
        $connection = $this->createConnection($dsn, $config, $this->getOptions());
        $connection->exec("use `{$config['database']}`;");
        return $connection;
    }
    protected function getDsn(array $config): string
    {
        $port = $config['port'] ?? 3306;

        return "mysql:host={$config['host']};port={$port};dbname={$config['database']}";
    }
}
