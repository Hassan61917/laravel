<?php

namespace Src\Main\Foundation;

use Closure;
use Src\Main\Container\Container;
use Src\Main\Container\IContainer;
use Src\Main\Foundation\Configuration\ApplicationBuilder;
use Src\Main\Support\ServiceProvider;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;

class Application extends Container implements IApplication
{
    const VERSION = '1.0.0';
    protected string $basePath;
    protected string $appPath;
    protected string $bootstrapPath;
    protected string $configPath;
    protected string $databasePath;
    protected string $publicPath;
    protected string $storagePath;
    protected string $environmentPath;
    protected string $environmentFile = ".env";
    protected string $namespace = "App";
    protected bool $booted = false;
    protected bool $hasBeenBootstrapped = false;
    protected bool $isRunningInConsole = false;
    protected array $serviceProviders = [];
    protected array $loadedProviders = [];
    protected array $bootstraps = [];
    protected IObserverList $bootingCallbacks;
    protected IObserverList $bootedCallbacks;
    protected IObserverList $terminatingCallbacks;

    public function __construct(
        string $basePath,
        protected array $baseServices = []
    ) {
        parent::__construct();
        $this->init($basePath);
    }
    public static function configure(string $basePath): ApplicationBuilder
    {
        $app = new static($basePath, require "baseServices.php");

        $builder = new ApplicationBuilder($app);


        $builder
            ->withBootstraps()
            ->withProviders();

        return $builder;
    }
    public function version(): string
    {
        return static::VERSION;
    }
    public function basePath(string $path = ''): string
    {
        return $this->joinPaths($this->basePath, $path);
    }
    public function setBasePath(string $basePath): static
    {
        $this->basePath = rtrim($basePath, '\/');

        return $this;
    }
    public function path(string $path = ''): string
    {
        return $this->joinPaths($this->appPath ?? $this->basePath('app'), $path);
    }
    public function setAppPath(string $path): static
    {
        $this->appPath = $path;

        $this->instance('path', fn() => $path);

        return $this;
    }
    public function bootstrapPath(string $path = ''): string
    {
        return $this->joinPaths($this->bootstrapPath, $path);
    }
    public function setBootstrapPath(string $path): static
    {
        $this->bootstrapPath = $path;

        $this->instance('path.bootstrap', $path);

        return $this;
    }
    public function configPath(string $path = ''): string
    {
        return $this->joinPaths($this->configPath ?? $this->basePath('config'), $path);
    }
    public function setConfigPath(string $path): static
    {
        $this->configPath = $path;

        $this->instance('path.config', $path);

        return $this;
    }
    public function databasePath(string $path = ''): string
    {
        return $this->joinPaths($this->databasePath ?? $this->basePath('database'), $path);
    }
    public function setDatabasePath(string $path): static
    {
        $this->databasePath = $path;

        $this->instance('path.database', $path);

        return $this;
    }
    public function publicPath(string $path = ''): string
    {
        return $this->joinPaths($this->publicPath ?? $this->basePath('public'), $path);
    }
    public function setPublicPath(string $path): static
    {
        $this->publicPath = $path;

        $this->instance('path.public', $path);

        return $this;
    }
    public function resourcePath(string $path = ''): string
    {
        return $this->joinPaths($this->basePath('resources'), $path);
    }
    public function storagePath(string $path = ''): string
    {
        return $this->joinPaths($this->storagePath ?? $this->basePath('storage'), $path);
    }
    public function setStoragePath(string $path): static
    {
        $this->storagePath = $path;

        $this->instance('path.storage', $path);

        return $this;
    }
    public function setNamespace(string $name): static
    {
        $this->namespace = $name;

        return $this;
    }
    public function getNamespace(): string
    {
        return $this->namespace;
    }
    public function bootstrapProviderPath(): string
    {
        return $this->bootstrapPath('providers.php');
    }
    public function environmentPath(): string
    {
        return $this->environmentPath ?? $this->basePath;
    }
    public function setEnvironmentPath(string $path): static
    {
        $this->environmentPath = $path;

        return $this;
    }
    public function environmentFile(): string
    {
        return $this->environmentFile;
    }
    public function setEnvironmentFile(string $file): static
    {
        $this->environmentFile = $file;

        return $this;
    }
    public function hasDebugModeEnabled(): bool
    {
        return (bool) $this['config']->get('app.debug');
    }
    public function register(ServiceProvider $provider, bool $force = false): ServiceProvider
    {
        $service = $this->getProvider($provider);

        if ($service && !$force) {
            return $service;
        }

        $this->registerProvider($provider);

        $this->markAsRegistered($provider);

        if ($this->isBooted()) {
            $provider->start();
        }

        return $provider;
    }
    public function booting(Closure $callback): void
    {
        $this->bootingCallbacks->add($callback);
    }
    public function booted(Closure $callback): void
    {
        $this->bootedCallbacks->add($callback);
    }
    public function boot(): void
    {
        if ($this->isBooted()) {
            return;
        }

        $this->fireAppCallbacks($this->bootingCallbacks);

        array_walk($this->serviceProviders, fn($provider) => $provider->start());

        $this->booted = true;

        $this->fireAppCallbacks($this->bootedCallbacks);
    }
    public function isBooted(): bool
    {
        return $this->booted;
    }
    public function bootstrap(array $bootstraps = []): void
    {
        if ($this->hasBeenBootstrapped) {
            return;
        }
        $this->bootstraps = array_merge($this->bootstraps, $bootstraps);
        $this->bootstrapWith($this->bootstraps);
    }
    public function hasBeenBootstrapped(): bool
    {
        return $this->hasBeenBootstrapped;
    }
    public function terminating(Closure $callback): void
    {
        $this->terminatingCallbacks->add($callback);
    }
    public function terminate(): void
    {
        $this->fireAppCallbacks($this->terminatingCallbacks);
    }
    public function getProviders(): array
    {
        return $this->serviceProviders;
    }
    public function addBootstrap(string $class): static
    {
        $this->bootstraps[] = $class;
        return $this;
    }
    public function runningInConsole(): bool
    {
        if (!$this->isRunningInConsole) {
            $this->isRunningInConsole =  PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
        }

        return $this->isRunningInConsole;
    }
    public function addBaseService(string $class): static
    {
        $provider = new $class($this);

        if ($provider instanceof ServiceProvider) {
            $this->baseServices[] = $provider;
        }

        return $this;
    }
    public function getLoadedProviders(): array
    {
        return $this->loadedProviders;
    }
    public function isProviderLoaded(ServiceProvider $provider): bool
    {
        $class = get_class($provider);
        return isset($this->loadedProviders[$class]);
    }
    public function registerProviders(): void
    {
        $providers = $this["config"]["app.providers"];

        $this->registerServiceProviders($providers);
    }
    protected function init(string $basePath): void
    {
        $this->setBasePath($basePath);
        $this->registerObservers();
        $this->registerBaseBindings();
        $this->registerBaseServiceProviders();
        $this->bindPaths();
    }
    protected function registerObservers(): void
    {
        $this->bootingCallbacks = new ObserverList();
        $this->bootedCallbacks = new ObserverList();
        $this->terminatingCallbacks = new ObserverList();
    }
    protected function registerBaseBindings(): void
    {
        static::setInstance($this);

        $this->alias("app", IContainer::class, IApplication::class, Application::class);

        $this->instance('app', $this);
    }
    protected function registerBaseServiceProviders(): void
    {
        $this->registerServiceProviders($this->baseServices);
    }
    protected function bindPaths(): void
    {
        $this->instance('path', $this->path());
        $this->instance('path.base', $this->basePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.database', $this->databasePath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.resources', $this->resourcePath());
        $this->instance('path.storage', $this->storagePath());
        $this->setBootstrapPath($this->basePath('bootstrap'));
    }
    protected function joinPaths(string $basePath, string $path = ''): string
    {
        return join_paths($basePath, $path);
    }
    protected function getProvider(ServiceProvider $provider): ?ServiceProvider
    {
        $class = get_class($provider);
        return $this->serviceProviders[$class] ?? null;
    }
    protected function registerProvider(ServiceProvider $provider): void
    {
        foreach ($provider->getAliases() as $alias => $abstracts) {
            $this->alias($alias, ...$abstracts);
        }

        $provider->register();
    }
    protected function markAsRegistered(ServiceProvider $provider): void
    {
        $class = get_class($provider);
        $this->serviceProviders[$class] = $provider;
        $this->loadedProviders[$class] = true;
    }
    protected function fireAppCallbacks(IObserverList $observer): void
    {
        $observer->run();
    }
    protected function bootstrapWith(array $items): void
    {
        $this->hasBeenBootstrapped = true;
        foreach ($items as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }
    protected function registerServiceProviders($providers): void
    {
        foreach ($providers as $provider) {
            $this->register(new $provider($this));
        }
    }
}
