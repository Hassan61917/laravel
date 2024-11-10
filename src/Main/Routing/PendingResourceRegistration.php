<?php

namespace Src\Main\Routing;

class PendingResourceRegistration
{
    protected bool $registered = false;
    public function __construct(
        protected ResourceRegistrar $registrar,
        protected string $name,
        protected string $controller,
        protected array $options = []
    ) {}
    public function only(string ...$methods): static
    {
        $this->options['only'] = $methods;

        return $this;
    }
    public function except(string ...$methods): static
    {
        $this->options['except'] = $methods;

        return $this;
    }
    public function names(string $name): static
    {
        $this->options['name'] = $name;

        return $this;
    }
    public function name(string $method, string $name): static
    {
        $this->options['names'][$method] = $name;

        return $this;
    }
    public function middleware(string ...$middlewares): static
    {
        $this->options['middlewares'] = $middlewares;

        return $this;
    }
    public function register(): void
    {
        $this->registered = true;

        $this->registrar->register(
            $this->name,
            $this->controller,
            $this->options
        );
    }
    public function __destruct()
    {
        if (! $this->registered) {
            $this->register();
        }
    }
}
