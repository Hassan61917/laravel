<?php

namespace Src\Main\Foundation\Bootstraps;

use Src\Main\Foundation\Application;

interface IBootstrap
{
    public function bootstrap(Application $app): void;
}
