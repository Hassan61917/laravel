<?php

namespace Src\Main\Cache\Services;

interface ICacheStoreFactory
{
    public function make(string $name, array $config = []): ICacheStore;
}
