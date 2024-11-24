<?php

return [

    'default' => env('LOG_CHANNEL', 'custom'),

    'loggers' => [
        'custom' => [
            'driver' => 'custom',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
    ],
];
