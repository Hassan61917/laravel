<?php

namespace Src\Main\Log;

use Src\Main\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "log" => [LogManager::class],
            "logger" => [ILogger::class]
        ];
    }
    public function register(): void
    {
        $this->app->singleton(ILoggerFactory::class, LoggerFactory::class);
        $this->app->singleton('log', fn($app) => new LogManager($app, $app[ILoggerFactory::class]));
        $this->app->singleton("logger", fn($app) => $app["log"]->getDriver());
    }
}
