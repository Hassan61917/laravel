<?php

namespace Src\Main\Foundation\Bootstraps;

use Src\Main\Facade\Facade;
use Src\Main\Foundation\Application;

class RegisterFacades implements IBootstrap
{
    public function bootstrap(Application $app): void
    {
        Facade::clearInstances();
        Facade::setApp($app);
    }
}
