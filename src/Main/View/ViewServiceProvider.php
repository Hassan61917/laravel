<?php

namespace Src\Main\View;

use Src\Main\Support\ServiceProvider;
use Src\Main\View\Compilers\CompilerFactory;
use Src\Main\View\Compilers\ICompilerFactory;
use Src\Main\View\Engines\EngineFactory;
use Src\Main\View\Engines\EngineManager;
use Src\Main\View\Engines\IEngineFactory;
use Src\Main\View\Finders\FileFinder;
use Src\Main\View\Finders\IFinder;
use Src\Main\View\Finders\ViewFinder;

class ViewServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "view" => [ViewManager::class, IViewFactory::class]
        ];
    }
    public function register(): void
    {
        $this->registerViewFinder();
        $this->registerEngineManage();
        $this->registerViewManager();
        $this->registerCompilerFactory();
    }
    protected function registerViewFinder(): void
    {
        $this->app->singleton("viewFinder", function ($app) {
            return new FileFinder($app);
        });

        $this->app->singleton(IFinder::class, function ($app) {
            return new ViewFinder($app["viewFinder"]);
        });
    }
    protected function registerEngineManage(): void
    {
        $this->app->singleton(IEngineFactory::class, EngineFactory::class);
        $this->app->singleton(EngineManager::class);
    }
    protected function registerViewManager(): void
    {
        $this->app->singleton("view", function ($app) {
            return new ViewManager(
                $app[IFinder::class],
                $app[EngineManager::class],
                $app
            );
        });
    }
    protected function registerCompilerFactory(): void
    {
        $this->app->singleton(ICompilerFactory::class, CompilerFactory::class);
    }
}
