<?php

use Src\Main\Foundation\Application;

$basePath = dirname(__DIR__);

$builder = Application::configure($basePath);

return $builder->create();
