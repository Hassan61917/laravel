<?php

use Src\Main\Cookie\Middlewares\AddQueuedCookiesToResponse;
use Src\Main\Cookie\Middlewares\EncryptCookies;
use Src\Main\Routing\Middlewares\RouteParameterBinding;
use Src\Main\Session\Middlewares\ShareErrorsFromSession;
use Src\Main\Session\Middlewares\StartSession;
use Src\Main\Session\Middlewares\VerifyCsrfToken;

return [
    'web' => array_values(array_filter([
        EncryptCookies::class,
        AddQueuedCookiesToResponse::class,
        StartSession::class,
        ShareErrorsFromSession::class,
        VerifyCsrfToken::class,
        RouteParameterBinding::class
    ])),
    'api' => array_values(array_filter([
        RouteParameterBinding::class
    ])),
];
