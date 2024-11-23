<?php

namespace Src\Main\Foundation\Configuration;

use Closure;
use Src\Main\Facade\Facades\Route;
use Src\Main\Foundation\Application;
use Src\Main\Foundation\Bootstraps\RegisterProviders;
use Src\Main\Foundation\Console\ConsoleKernel;
use Src\Main\Foundation\Console\IConsoleKernel;
use Src\Main\Foundation\Http\HttpKernel;
use Src\Main\Foundation\Http\IHttpKernel;
use Src\Main\Foundation\Providers\EventServiceProvider;
use Src\Main\Foundation\Providers\RouteServiceProvider;
use Src\Main\Http\MiddlewareContainer\MiddlewareContainer;

class ApplicationBuilder
{
    public function __construct(
        protected Application $app
    ) {}
    public function withBootstraps(): static
    {
        foreach ($this->loadBootstraps() as $bootstrap) {
            $this->app->addBootstrap($bootstrap);
        }
        return $this;
    }
    public function withProviders(): static
    {
        RegisterProviders::setProviderPath(
            $this->app->bootstrapProviderPath()
        );

        return $this;
    }
    public function withKernels(): static
    {
        $this->app->singleton(IHttpKernel::class, HttpKernel::class);

        $this->app->singleton(IConsoleKernel::class, ConsoleKernel::class);

        return $this;
    }
    public function withMiddlewares(Closure $closure): static
    {
        $callback = function (HttpKernel $kernel) use ($closure) {
            $container = new MiddlewareContainer();

            $container->redirectGuestsTo("login");

            $container->redirectUsersTo(route("home"));

            call_user_func($closure, $container);

            $kernel->setMiddlewares($container->getGlobalMiddlewares());

            $kernel->setMiddlewareGroups($container->getMiddlewareGroups());

            $kernel->setMiddlewareAliases($container->getMiddlewareAliases());

            $kernel->syncMiddlewares();
        };

        $this->app->afterResolving(HttpKernel::class, $callback);

        return $this;
    }
    public function withRouting(
        ?string $web = null,
        ?string $api = null,
        string $apiPrefix = "api"
    ): static {

        $using = $this->buildRoutingCallback($web, $api, $apiPrefix);

        RouteServiceProvider::setAlwaysLoadRoutesUsing($using);

        $this->app->booting(function () {
            $this->app->register(new RouteServiceProvider($this->app), true);
        });

        return $this;
    }
    public function withCommands(array $paths = []): static
    {
        $paths[] = $this->app->path('Console/Commands');

        $this->app->afterResolving(
            ConsoleKernel::class,
            function (ConsoleKernel $kernel) use ($paths) {
                [$routes, $paths] = collect($paths)->partition(fn($path) => is_file($path));
                $this->app->booted(function () use ($kernel, $routes, $paths) {
                    $kernel->addCommandPaths(...$paths);
                    $kernel->addCommandRoutePaths(...$routes);
                });
            }
        );

        return $this;
    }
    public function withEvents(): static
    {
        $this->app->booting(function () {
            $this->app->register(new EventServiceProvider($this->app));
        });

        return $this;
    }
    public function create(): Application
    {
        return $this->app;
    }
    protected function loadBootstraps(): array
    {
        $path = dirname(__DIR__) . "/bootstraps.php";
        return require_once $path;
    }
    protected function buildRoutingCallback(?string $web = null, ?string $api = null, ?string $apiPrefix = null): \Closure
    {
        return function () use ($web, $api, $apiPrefix) {
            if ($web && realpath($web)) {
                Route::middleware('web')->group($web);
            }

            if ($api && realpath($api)) {
                Route::middleware('api')->prefix($apiPrefix)->group($api);
            }
        };
    }
}
