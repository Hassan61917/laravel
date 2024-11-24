<?php

use Src\Main\Support\ServiceProvider;

return [
    'key' => env('APP_KEY'),

    'debug' => (bool) env('APP_DEBUG', false),

    'cipher' => 'AES-256-CBC',

    "providers" => ServiceProvider::defaultProviders()->merge([])->toArray(),
];
