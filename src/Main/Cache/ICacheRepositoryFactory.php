<?php

namespace Src\Main\Cache;

use Src\Main\Cache\Services\ICacheStore;

interface ICacheRepositoryFactory
{
    public function make(ICacheStore $store, array $config): ICacheRepository;
}
