<?php

namespace Src\Main\Pagination;

use Src\Main\Foundation\IApplication;

class PaginationState
{
    public static function resolveUsing(IApplication $app): void
    {
        static::resolvePaginator($app);
    }
    protected static function resolvePaginator(IApplication $app): void
    {
        Paginator::viewFactoryResolver(fn() => $app['view']);
        Paginator::currentPathResolver(fn() => $app['request']->url());
        Paginator::queryStringResolver(fn() => $app['request']->getItems("query"));
        Paginator::currentPageResolver(function ($pageName = 'page') use ($app) {
            return (int) $app['request']->input($pageName, 1);
        });
    }
}
