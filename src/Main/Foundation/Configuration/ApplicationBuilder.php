<?php

namespace Src\Main\Foundation\Configuration;

use Src\Main\Foundation\Application;
use Src\Main\Foundation\Bootstraps\RegisterProviders;

class ApplicationBuilder
{
    public function __construct(
        protected Application $app
    ) {}
    public function withBootstraps(): static
    {
        foreach ($this->loadBootstraps() as $bootstrap) {
            $this->app->addBootstrap($bootstrap);
        }
        return $this;
    }
    public function withProviders(): static
    {
        RegisterProviders::setProviderPath(
            $this->app->bootstrapProviderPath()
        );

        return $this;
    }
    public function create(): Application
    {
        return $this->app;
    }
    protected function loadBootstraps(): array
    {
        $path = dirname(__DIR__) . "/bootstraps.php";
        return require_once $path;
    }
}
