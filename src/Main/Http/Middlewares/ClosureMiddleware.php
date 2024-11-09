<?php

namespace Src\Main\Http\Middlewares;

use Closure;
use Exception;
use Src\Main\Debug\IExceptionHandler;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;

class ClosureMiddleware extends Middleware
{
    public function __construct(
        protected Closure $closure
    ) {}
    protected function doHandle(Request $request, ...$args): Response
    {
        return call_user_func($this->closure, $request);
    }
}
