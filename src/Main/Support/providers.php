<?php

use Src\Main\Cookie\CookieServiceProvider;
use Src\Main\Encryption\EncryptionServiceProvider;
use Src\Main\Hashing\HashServiceProvider;
use Src\Main\Http\HttpServiceProvider;
use Src\Main\Session\SessionServiceProvider;
use Src\Main\View\ViewServiceProvider;
use Src\Main\Foundation\Providers\ConsoleServiceProvider;
use Src\Main\Translation\TranslationServiceProvider;
use Src\Main\Validation\ValidationServiceProvider;
use Src\Main\Pagination\PaginationServiceProvider;
use Src\Main\Database\DatabaseServiceProvider;
use Src\Main\Database\MigrationServiceProvider;

return [
    HttpServiceProvider::class,
    EncryptionServiceProvider::class,
    HashServiceProvider::class,
    CookieServiceProvider::class,
    SessionServiceProvider::class,
    ViewServiceProvider::class,
    ConsoleServiceProvider::class,
    TranslationServiceProvider::class,
    ValidationServiceProvider::class,
    PaginationServiceProvider::class,
    DatabaseServiceProvider::class,
    MigrationServiceProvider::class,
];
