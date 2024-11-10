<?php

use Src\Main\Foundation\Application;
use Src\Main\Http\MiddlewareContainer\MiddlewareContainer;

$basePath = dirname(__DIR__);

$builder = Application::configure($basePath);

$builder
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddlewares(function (MiddlewareContainer $middleware) {
        // add your middlewares here
    });


return $builder->create();
