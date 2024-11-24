<?php

namespace Src\Main\Debug;

use Src\Main\Debug\Renderers\SimpleRenderer;
use Src\Main\Support\ServiceProvider;

class DebugServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerLoader();
        $this->registerRenderer();
    }
    protected function registerLoader(): void
    {
        $this->app->singleton(IOperationLoader::class, OperationLoader::class);
    }
    protected function registerRenderer(): void
    {
        $this->app->singleton(IExceptionRenderer::class, SimpleRenderer::class);
    }
}
