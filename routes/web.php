<?php

use Src\Main\Facade\Facades\Route;

use App\Http\Controllers\HomeController;

Route::get("/", [HomeController::class, "index"])->name("home");
