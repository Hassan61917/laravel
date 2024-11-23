<?php

namespace Src\Main\Cache;

use Src\Main\Cache\Services\ICacheStoreFactory;
use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;

class CacheManager extends DriverManager
{
    protected string $configName = "cache";
    public function __construct(
        IContainer $container,
        protected ICacheStoreFactory $storeFactory,
        protected ICacheRepositoryFactory $repositoryFactory,
    ) {
        parent::__construct($container);
    }
    protected function create(string $driver): ICacheRepository
    {
        $config = $this->getConfig("stores.$driver");

        $store = $this->storeFactory->make($driver, $config);

        return $this->repositoryFactory->make($store, $config);
    }
}
