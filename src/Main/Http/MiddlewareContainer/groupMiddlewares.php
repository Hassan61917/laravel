<?php

use Src\Main\Routing\Middlewares\RouteParameterBinding;

return [
    'web' => array_values(array_filter([
        RouteParameterBinding::class
    ])),
    'api' => array_values(array_filter([
        RouteParameterBinding::class
    ])),
];
