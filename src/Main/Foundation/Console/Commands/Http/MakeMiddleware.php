<?php

namespace Src\Main\Foundation\Console\Commands\Http;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeMiddleware extends AbstractMakeGenerator
{
    protected string $path = "Http/Middlewares";
    protected string $type = "Middleware";
    protected string $stubsPath = "Http";
    protected string $description = 'Create a new middleware class';
    protected function getDefaultStub(): string
    {
        return "middleware.stub";
    }
}
