<?php

use Src\Main\Facade\Facades\Artisan;

Artisan::command('greet', function () {
    $this->write("Hi there what's up");
})->purpose('Display a greeting message');
