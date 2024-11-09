<?php

namespace Src\Main\Foundation\Http;

use Closure;
use Src\Main\Facade\Facade;
use Src\Main\Foundation\IApplication;
use Src\Main\Http\Pipeline;
use Src\Main\Http\Request;
use Src\Main\Http\Response;

class HttpKernel implements IHttpKernel
{
    protected array $middlewares = [];
    protected array $middlewareGroups = [];
    protected array $middlewareAliases = [];
    public function __construct(
        protected IApplication $app,
    ) {}
    public function prependMiddleware(string $middleware): static
    {
        if (!in_array($middleware, $this->middlewares)) {
            array_unshift($this->middlewares, $middleware);
        }

        return $this;
    }
    public function pushMiddleware(string $middleware): static
    {
        if (!in_array($middleware, $this->middlewares)) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }
    public function hasMiddleware(string $middleware): bool
    {
        return in_array($middleware, $this->middlewares);
    }
    public function setMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
    }
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
    public function setMiddlewareGroups(array $middlewareGroups): void
    {
        $this->middlewareGroups = $middlewareGroups;
    }
    public function getMiddlewareGroups(): array
    {
        return $this->middlewareGroups;
    }
    public function setMiddlewareAliases(array $middlewareAliases): void
    {
        $this->middlewareAliases = $middlewareAliases;
    }
    public function syncMiddlewares(): void {}
    public function getMiddlewareAliases(): array
    {
        return $this->middlewareAliases;
    }
    public function getApp(): IApplication
    {
        return $this->app;
    }
    public function handle(Request $request): Response
    {
        $request->enableHttpMethodOverride();

        $this->bootRequest($request);

        return $this->sendRequestToRouter($request);
    }
    public function terminate(Request $request, Response $response): void
    {
        $this->app->terminate();
    }
    protected function bootRequest(Request $request): void
    {
        Facade::clearInstance('request');

        $this->app->alias("request", Request::class);

        $this->app->instance('request', $request);

        $this->app->bootstrap();
    }
    protected function sendRequestToRouter(Request $request): Response
    {
        $pipeline = new Pipeline($this->app);

        $pipeline->addPipe(...$this->middlewares);

        return $pipeline->handle($request, $this->dispatchToRouter());
    }
    protected function dispatchToRouter(): Closure
    {
        return function (Request $request) {
            return new Response("it works");
        };
    }
}
