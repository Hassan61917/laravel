<?php

namespace Src\Main\Queue;

use Src\Main\Cache\ICacheRepository;
use Src\Main\Debug\IExceptionHandler;
use Src\Main\Queue\Connectors\ConnectorFactory;
use Src\Main\Queue\Connectors\IConnectorFactory;
use Src\Main\Support\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "queue" => [IQueueFactory::class, QueueManager::class],
            "queue.connection" => [IQueueService::class]
        ];
    }
    public function register(): void
    {
        $this->registerQueueManager();
        $this->registerConnection();
        $this->registerWorker();
    }
    protected function registerQueueManager(): void
    {
        $this->app->singleton(IConnectorFactory::class, ConnectorFactory::class);

        $this->app->singleton(IPayloadCreator::class, PayloadCreator::class);

        $this->app->singleton(IJobHandler::class, JobHandler::class);

        $this->app->singleton(
            'queue',
            fn($app) => new QueueManager($app, $app[IConnectorFactory::class])
        );
    }
    protected function registerConnection(): void
    {
        $this->app->singleton(
            'queue.connection',
            fn($app) => $app['queue']->getDriver()
        );
    }
    protected function registerWorker(): void
    {
        $this->app->singleton('queue.worker', function ($app) {
            return new Worker(
                $app['queue'],
                $app[IExceptionHandler::class],
                $app[ICacheRepository::class]
            );
        });
    }
}
