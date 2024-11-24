<?php

namespace Src\Main\Http\Middlewares;

use Closure;
use Src\Main\Debug\IExceptionHandler;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Throwable;

class ClosureMiddleware extends Middleware
{
    public function __construct(
        protected Closure $closure
    ) {}
    protected function doHandle(Request $request, ...$args): Response
    {
        try {
            return call_user_func($this->closure, $request);
        } catch (Throwable $e) {
            return $this->handleException($e, $request);
        }
    }
    protected function handleException(Throwable $e, Request $request): Response
    {
        $handler = app(IExceptionHandler::class);

        $handler->handle($request, $e);

        return app("exception.response");
    }
}
