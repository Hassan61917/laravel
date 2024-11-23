<?php

use Src\Main\Auth\Middlewares\Authenticate;
use Src\Main\Auth\Middlewares\Authorize;

return [
    "auth" => Authenticate::class,
    "can" => Authorize::class,
];
