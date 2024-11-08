<?php

use Src\Main\Support\ServiceProvider;

return [
    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    "providers" => ServiceProvider::defaultProviders()->merge([])->toArray(),
];
