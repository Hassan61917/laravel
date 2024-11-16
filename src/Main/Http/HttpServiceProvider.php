<?php

namespace Src\Main\Http;

use Src\Main\Http\Redirect\Redirector;
use Src\Main\Support\ServiceProvider;

class HttpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerUrlGenerator();
        $this->registerRedirector();
    }
    protected function registerUrlGenerator(): void
    {
        $this->app->singleton('url', function ($app) {
            $routes = $app['router']->getRoutes();
            return new UrlGenerator(
                $routes,
                $app["request"],
                $app["session.store"],
                $app["config"]["app.key"],
                $app['config']['app.asset_url']
            );
        });
    }
    protected function registerRedirector(): void
    {
        $this->app->singleton('redirect', function ($app) {
            return new Redirector($app['url'], $app["session.store"]);
        });
    }
}
