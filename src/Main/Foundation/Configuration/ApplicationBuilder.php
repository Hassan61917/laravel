<?php

namespace Src\Main\Foundation\Configuration;

use Src\Main\Foundation\Application;

class ApplicationBuilder
{
    public function __construct(
        protected Application $app
    ) {}
    public function create(): Application
    {
        return $this->app;
    }
}
