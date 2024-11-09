<?php

namespace Src\Main\Http;

use Closure;
use Src\Main\Container\IContainer;
use Src\Main\Http\Middlewares\ClosureMiddleware;

class Pipeline
{
    protected array $middlewares = [];
    protected array $pipes = [];
    public function __construct(
        protected IContainer $container
    ) {}
    public function addMiddleware(Middleware ...$middlewares): static
    {
        array_push($this->middlewares, ...$middlewares);
        return $this;
    }
    public function addPipe(string ...$pipes): static
    {
        array_push($this->pipes, ...$pipes);
        return $this;
    }
    public function handle(Request $request, Closure $callback): Response
    {
        $this->toMiddleware();

        $this->bootMiddlewares($callback);

        return $this->middlewares[0]->handle($request);
    }
    protected function toMiddleware(): void
    {
        foreach ($this->pipes as $pipe) {
            $this->addMiddleware($this->container->make($pipe));
        }
    }
    protected function bootMiddlewares(\Closure $callback): void
    {
        $this->addMiddleware(new ClosureMiddleware($callback));

        for ($i = 0; $i < count($this->middlewares) - 1; $i++) {
            $current = $this->middlewares[$i];
            $next = $this->middlewares[$i + 1];
            $current->setNext($next);
        }
    }
}
