<?php

namespace Src\Main\Hashing;

use Src\Main\Hashing\Drivers\HashDriverFactory;
use Src\Main\Hashing\Drivers\IHashDriverFactory;
use Src\Main\Support\ServiceProvider;

class HashServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "hash" => [HashManager::class]
        ];
    }
    public function register(): void
    {
        $this->app->singleton(IHashDriverFactory::class, HashDriverFactory::class);

        $this->app->singleton('hash', fn($app) => new HashManager($app, $app[IHashDriverFactory::class]));

        $this->app->singleton('hash.driver', fn($app) => $app['hash']->getDriver());
    }
}
