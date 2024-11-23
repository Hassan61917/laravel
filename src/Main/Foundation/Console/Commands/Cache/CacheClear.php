<?php

namespace Src\Main\Foundation\Console\Commands\Cache;

use Src\Main\Cache\CacheManager;

class CacheClear extends AbstractCacheCommand
{
    protected string $description = 'Flush the application cache';
    public function __construct(
        protected CacheManager $cache
    ) {
        parent::__construct($this->cache, "cache:clear");
    }
    public function handle(): void
    {
        $this->cache()->clear();

        $this->output->write('Application cache cleared successfully.');
    }
}
