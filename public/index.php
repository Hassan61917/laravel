<?php

use Src\Main\Http\Request;

require_once  __DIR__ . "/../vendor/autoload.php";

$app =  require_once __DIR__ . "/../bootstrap/app.php";

$request = Request::capture();

$app->handleRequest($request);
