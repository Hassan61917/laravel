<?php

namespace Src\Main\Database;

use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Connections\ConnectionFactory;
use Src\Main\Database\Connections\IConnectionFactory;
use Src\Main\Database\Connectors\ConnectorFactory;
use Src\Main\Database\Connectors\IConnectorFactory;
use Src\Main\Database\Schema\Builders\SchemaBuilder;
use Src\Main\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "db" => [IConnectionResolver::class, DatabaseManager::class],
            "db.connection" => [Connection::class],
            "db.schema" => [SchemaBuilder::class]
        ];
    }
    public function register(): void
    {
        $this->registerConnection();
        $this->registerDatabase();
    }
    protected function registerConnection(): void
    {
        $this->app->singleton(IConnectorFactory::class, ConnectorFactory::class);

        $this->app->singleton(IConnectionFactory::class, ConnectionFactory::class);
    }
    protected function registerDatabase(): void
    {
        $this->app->singleton("db", fn($app) => new DatabaseManager($app, $app[IConnectionFactory::class]));

        $this->app->singleton("db.connection", fn($app) => $app["db"]->connection());

        $this->app->bind('db.schema', fn($app) => $app['db.connection']->getSchemaBuilder());
    }
}
