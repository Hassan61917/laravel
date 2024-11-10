<?php

namespace App\Http\Controllers;

use Src\Main\Routing\Route\IActionResult;

class HomeController extends Controller
{
    public function index(): IActionResult
    {
        return response("Home Page");
    }
}
