<?php

namespace Src\Main\Cache\Services;

use Src\Main\Cache\Services\Array\ArrayStore;
use Src\Main\Cache\Services\Database\DatabaseStore;
use Src\Main\Cache\Services\File\FileStore;
use Src\Main\Container\IContainer;
use Src\Main\Filesystem\Filesystem;

class CacheStoreFactory implements ICacheStoreFactory
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function make(string $name, array $config = []): ICacheStore
    {
        return match ($name) {
            "array" => new ArrayStore($config),
            "database" => new DatabaseStore($this->container["db.connection"], $config),
            "file" => new FileStore(new Filesystem(), $config),
        };
    }
}
