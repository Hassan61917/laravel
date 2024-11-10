<?php

namespace Src\Main\Routing\Route;

use Src\Main\Http\Response;

interface IActionResult
{
    public function get(): Response;
}
