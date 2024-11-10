<?php

namespace Src\Main\Routing;

use Src\Main\Routing\Route\Actions\ActionFactory;
use Src\Main\Routing\Route\Actions\IActionFactory;
use Src\Main\Support\ServiceProvider;

class RoutingServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "router" => [Router::class]
        ];
    }
    public function register(): void
    {
        $this->registerRouteCollection();
        $this->registerActionFactory();
        $this->registerParameterBinder();
        $this->registerRouter();
    }
    protected function registerRouteCollection(): void
    {
        $this->app->singleton(IRouteCollection::class, RouteCollection::class);
    }
    protected function registerActionFactory(): void
    {
        $this->app->singleton(IActionFactory::class, ActionFactory::class);
    }
    protected function registerParameterBinder():void
    {
        $this->app->singleton(IRouteParameterBinder::class, RouteParameterBinder::class);
    }
    protected function registerRouter(): void
    {
        $this->app->singleton("router", function ($app) {
            return new Router(
                $app,
                $app[IRouteCollection::class],
                $app[IRouteParameterBinder::class]
            );
        });
    }
}
