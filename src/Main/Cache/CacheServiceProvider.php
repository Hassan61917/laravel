<?php

namespace Src\Main\Cache;

use Src\Main\Cache\Services\CacheStoreFactory;
use Src\Main\Cache\Services\ICacheStoreFactory;
use Src\Main\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "cache" => [CacheManager::class],
            "cache.store" => [ICacheRepository::class, CacheRepository::class]
        ];
    }
    public function register(): void
    {
        $this->registerCacheManager();
        $this->registerCacheStore();
    }
    protected function registerCacheManager(): void
    {
        $this->app->singleton(ICacheStoreFactory::class, CacheStoreFactory::class);
        $this->app->singleton(ICacheRepositoryFactory::class, CacheRepositoryFactory::class);
        $this->app->singleton("cache", fn($app) => new CacheManager(
            $app,
            $app[ICacheStoreFactory::class],
            $app[ICacheRepositoryFactory::class],
        ));
    }
    protected function registerCacheStore(): void
    {
        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->getDriver();
        });
    }
}
