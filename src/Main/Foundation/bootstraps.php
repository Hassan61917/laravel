<?php

use Src\Main\Foundation\Bootstraps\BootProviders;
use Src\Main\Foundation\Bootstraps\LoadConfiguration;
use Src\Main\Foundation\Bootstraps\LoadEnvironment;
use Src\Main\Foundation\Bootstraps\RegisterFacades;
use Src\Main\Foundation\Bootstraps\RegisterProviders;

return [
    LoadEnvironment::class,
    LoadConfiguration::class,
    RegisterFacades::class,
    RegisterProviders::class,
    BootProviders::class
];
