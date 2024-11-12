<?php

use Src\Main\Cookie\CookieServiceProvider;
use Src\Main\Encryption\EncryptionServiceProvider;
use Src\Main\Hashing\HashServiceProvider;
use Src\Main\Session\SessionServiceProvider;

return [
    EncryptionServiceProvider::class,
    HashServiceProvider::class,
    CookieServiceProvider::class,
    SessionServiceProvider::class,
];
