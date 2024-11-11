<?php

namespace Src\Main\Foundation;

use Closure;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Src\Main\Container\IContainer;

abstract class DriverManager
{
    protected string $configName = "";
    protected bool $shouldBeCached = true;
    protected array $customDrivers = [];
    protected array $cachedDrivers = [];
    protected array $config;
    public function __construct(
        protected IContainer $container
    ) {
        $this->config = empty($this->configName) ? [] : $this->container["config"][$this->configName];
    }
    public function getDriver(?string $driver = null): object
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if (!$this->hasDriver($driver)) {
            $service = $this->createDriver($driver);

            if (!$this->shouldBeCached) {
                return $service;
            }

            $this->addDriver($driver, $service);
        }

        return $this->getCachedDriver($driver);
    }
    public function extend(string $driver, Closure $callback): static
    {
        $this->customDrivers[$driver] = $callback;

        return $this;
    }
    public function getCachedDrivers(): array
    {
        return $this->cachedDrivers;
    }
    public function getCustomDrivers(): array
    {
        return $this->customDrivers;
    }
    public function getContainer(): IContainer
    {
        return $this->container;
    }
    public function forgetDrivers(): static
    {
        $this->cachedDrivers = [];

        return $this;
    }
    public function addDriver(string $driver, object $service): void
    {
        $this->cachedDrivers[$driver] = $service;
    }
    public function forgetDriver(string $driver): static
    {
        unset($this->cachedDrivers[$driver]);

        return $this;
    }
    public function forgetCustomDriver(string $driver): static
    {
        unset($this->customDrivers[$driver]);

        return $this;
    }
    public function hasDriver(string $driver): bool
    {
        return isset($this->cachedDrivers[$driver]);
    }
    public function hasCustomDriver(string $driver): bool
    {
        return isset($this->customDrivers[$driver]);
    }
    public function getDefaultDriver(): ?string
    {
        return $this->config["default"] ?? null;
    }
    public function setDefaultDriver(string $name): void
    {
        $this->config["default"] = $name;
    }
    public function getConfig(?string $key = null): array
    {
        if (is_null($key)) {
            return $this->config;
        }

        return Arr::get($this->config, $key) ?? [];
    }
    protected function getCachedDriver(string $driver): object
    {
        return $this->cachedDrivers[$driver];
    }
    protected function createDriver(string $driver): object
    {
        if ($this->hasCustomDriver($driver)) {
            return $this->callCustomCreator($driver);
        }

        $driver = $this->create($driver);

        if ($driver) {
            return $driver;
        }

        throw new InvalidArgumentException("Driver [$driver] not supported.");
    }
    protected function callCustomCreator(string $driver): object
    {
        return $this->customDrivers[$driver]($this);
    }
    protected abstract function create(string $driver): object;
    public function __call(string $method, array $parameters): mixed
    {
        return $this->getDriver()->$method(...$parameters);
    }
}
