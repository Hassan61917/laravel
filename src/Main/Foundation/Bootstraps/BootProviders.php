<?php

namespace Src\Main\Foundation\Bootstraps;

use Src\Main\Foundation\Application;

class BootProviders implements IBootstrap
{
    public function bootstrap(Application $app): void
    {
        $app->boot();
    }
}
