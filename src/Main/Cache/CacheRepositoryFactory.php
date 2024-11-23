<?php

namespace Src\Main\Cache;

use Src\Main\Cache\Services\ICacheStore;

class CacheRepositoryFactory implements ICacheRepositoryFactory
{
    public function make(ICacheStore $store, array $config): ICacheRepository
    {
        if (empty($config)) {
            throw new \InvalidArgumentException("Config array is empty");
        }
        return new CacheRepository($store, $config);
    }
}
