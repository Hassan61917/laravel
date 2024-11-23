<?php

namespace Src\Main\Foundation\Console\Commands\Cache;

use Src\Main\Cache\CacheManager;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputMode;

class CacheForget extends AbstractCacheCommand
{
    protected string $description = 'Remove an item from the cache';
    public function __construct(
        protected CacheManager $cache
    ) {
        parent::__construct($this->cache, "cache:forget");
    }
    public function handle(): void
    {
        $key = $this->getInputArgument('key');

        $this->cache()->forget($key);

        $this->output->write("The ['$key'] key has been removed from the cache.");
    }
    protected function getArguments(): array
    {
        $arguments = parent::getArguments();
        return array_merge($arguments, [
            new InputArgument("key", 'The key to remove', InputMode::Required)
        ]);
    }
}
