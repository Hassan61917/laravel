<?php

namespace Src\Main\Pagination;

use Src\Main\Support\ServiceProvider;
use Src\Main\View\ViewManager;

class PaginationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->callAfterResolving("view", function (ViewManager $view) {
            $path = __DIR__ . "/resources/views";
            $view->addPath($path);
        });
    }
    public function register(): void
    {
        PaginationState::resolveUsing($this->app);
    }
}
