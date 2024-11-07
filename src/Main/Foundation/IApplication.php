<?php

namespace Src\Main\Foundation;

use Closure;
use Src\Main\Container\IContainer;
use Src\Main\Support\ServiceProvider;

interface IApplication extends IContainer
{
    public function version(): string;
    public function basePath(string $path = ''): string;
    public function path(string $path = ''): string;
    public function bootstrapPath(string $path = ''): string;
    public function configPath(string $path = ''): string;
    public function databasePath(string $path = ''): string;
    public function publicPath(string $path = ''): string;
    public function resourcePath(string $path = ''): string;
    public function storagePath(string $path = ''): string;
    public function getNamespace(): string;
    public function hasDebugModeEnabled(): bool;
    public function register(ServiceProvider $provider, bool $force = false): ServiceProvider;
    public function booting(Closure $callback): void;
    public function booted(Closure $callback): void;
    public function boot(): void;
    public function bootstrap(array $bootstraps = []): void;
    public function hasBeenBootstrapped(): bool;
    public function terminating(Closure $callback): void;
    public function terminate(): void;
}
