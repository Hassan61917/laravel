<?php

namespace Src\Main\Foundation\Http;

use Src\Main\Http\Request;
use Src\Main\Http\Response;

interface IHttpKernel
{
    public function handle(Request $request): Response;
    public function terminate(Request $request, Response $response): void;
}
