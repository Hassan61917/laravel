<?php

namespace Src\Main\Support;

class DefaultProviders
{
    public function __construct(
        protected array $providers = []
    ) {}
    public function merge(array $providers): static
    {
        $providers = array_merge($this->providers, $providers);
        return new static($providers);
    }
    public function push(string $provider): static
    {
        $this->providers[] = $provider;
        return $this;
    }
    public function toArray(): array
    {
        return $this->providers;
    }
}
