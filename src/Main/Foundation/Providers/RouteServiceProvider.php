<?php

namespace Src\Main\Foundation\Providers;

use Closure;
use Src\Main\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected static ?Closure $alwaysLoadRoutesUsing = null;
    public static function setAlwaysLoadRoutesUsing(?Closure $alwaysLoadRoutesUsing): void
    {
        self::$alwaysLoadRoutesUsing = $alwaysLoadRoutesUsing;
    }
    public function register(): void
    {
        $this->booted(function () {

            $this->loadRoutes();

            $this->app->booted(function () {
                $router = $this->app->make("router");
                $router->getRoutes()->refresh();
            });
        });
    }
    protected function loadRoutes(): void
    {
        if (self::$alwaysLoadRoutesUsing) {
            call_user_func(self::$alwaysLoadRoutesUsing);
        }
    }
}
