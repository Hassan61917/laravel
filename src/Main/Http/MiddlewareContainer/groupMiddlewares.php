<?php

use Src\Main\Cookie\Middlewares\AddQueuedCookiesToResponse;
use Src\Main\Cookie\Middlewares\EncryptCookies;
use Src\Main\Routing\Middlewares\RouteParameterBinding;
use Src\Main\Session\Middlewares\StartSession;

return [
    'web' => array_values(array_filter([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        RouteParameterBinding::class
    ])),
    'api' => array_values(array_filter([
        RouteParameterBinding::class
    ])),
];
