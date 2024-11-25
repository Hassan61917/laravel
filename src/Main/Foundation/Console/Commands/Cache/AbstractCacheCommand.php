<?php

namespace Src\Main\Foundation\Console\Commands\Cache;

use Src\Main\Cache\CacheManager;
use Src\Main\Cache\ICacheRepository;
use Src\Main\Console\AppCommand;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputMode;

abstract class AbstractCacheCommand extends AppCommand
{
    public function __construct(
        protected CacheManager $cache,
    ) {
        parent::__construct();
    }
    protected function cache(): ICacheRepository
    {
        return  $this->cache->getDriver($this->getInputArgument('store'));
    }
    protected function getArguments(): array
    {
        return [
            new InputArgument("store", 'The name of the store you would like to clear', InputMode::Optional)
        ];
    }
}
