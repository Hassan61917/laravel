<?php

namespace Src\Main\Routing;

use Illuminate\Support\Arr;
use Src\Main\Container\IContainer;
use Src\Main\Http\Pipeline;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Routing\Route\Route;
use Src\Main\Routing\Traits\RegisterGroupRoutes;
use Src\Main\Routing\Traits\RegisterRoutes;

class Router
{
    use RegisterRoutes,
        RegisterGroupRoutes;

    protected array $middlewares = [];
    protected array $middlewareGroups = [];
    protected Route $currentRoute;
    protected Request $currentRequest;
    public function __construct(
        protected IContainer $container,
        protected IRouteCollection $routes,
        protected IRouteParameterBinder $parameterBinder
    ) {}
    public function middlewareGroup(string $key, array $middlewares): static
    {
        $this->middlewareGroups[$key] = $middlewares;

        return $this;
    }
    public function aliasMiddleware(string $key, string $middleware): static
    {
        $this->middlewares[$key] = $middleware;

        return $this;
    }
    public function getRoutes(): IRouteCollection
    {
        return $this->routes;
    }
    public function dispatch(Request $request): Response
    {
        $this->currentRequest = $request;

        return $this->dispatchToRoute($request);
    }
    public function handleParameterBinding(Route $route): Route
    {
        return $this->parameterBinder->resolve($route);
    }
    public function has(string ...$names): bool
    {
        foreach ($names as $name) {
            if (!$this->getRoutes()->hasNamedRoute($name)) {
                return false;
            }
        }

        return true;
    }
    protected function dispatchToRoute(Request $request): Response
    {
        return $this->runRoute($request, $this->findRoute($request));
    }
    protected function findRoute(Request $request): Route
    {
        $route = $this->routes->match($request);

        $this->currentRoute = $route;

        $this->container->instance(Route::class, $route);

        return $route;
    }
    protected function runRoute(Request $request, Route $route): Response
    {
        $request->setRouteResolver(fn() => $route);

        $middlewares = $this->resolveMiddlewares($route);

        $pipeline = new Pipeline($this->container);

        return $pipeline
            ->addPipe(...$middlewares)
            ->handle($request, fn() => $route->run());
    }
    protected function resolveMiddlewares(Route $route): array
    {

        $middlewares = array_map(
            fn($name) => MiddlewareNameResolver::resolve($name, $this->middlewares, $this->middlewareGroups),
            $route->getMiddlewares()
        );

        return array_values(Arr::flatten($middlewares));
    }
    public function __call(string $name, array $arguments)
    {
        $registrar = new RouteRegistrar($this);
        return $registrar->addAttribute($name, $arguments);
    }
}
