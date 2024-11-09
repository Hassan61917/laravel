<?php

use Src\Main\Foundation\Application;
use Src\Main\Http\MiddlewareContainer\MiddlewareContainer;

$basePath = dirname(__DIR__);

$builder = Application::configure($basePath);

$builder
    ->withMiddlewares(function (MiddlewareContainer $middleware) {
        // add your middlewares here
    });


return $builder->create();
