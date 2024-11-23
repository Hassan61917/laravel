<?php

namespace Src\Main\Auth;

use InvalidArgumentException;
use Src\Main\Container\IContainer;

class CreateUserProvider
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function create(?string $provider = null): ?IUserProvider
    {
        $config = $this->getProviderConfiguration($provider);

        if (empty($config)) {
            return null;
        }

        $driver = $config['driver'] ?? null;

        return $this->makeProvider($driver, $config);
    }
    protected function getProviderConfiguration(string $provider): array
    {
        return $this->container['config']["auth.providers.$provider"] ?? [];
    }
    protected function makeProvider(mixed $driver, array $config): IUserProvider
    {
        return match ($driver) {
            'eloquent' => $this->createEloquentProvider($config),
            default => throw new InvalidArgumentException(
                "Authentication user provider $driver is not defined."
            ),
        };
    }
    protected function createEloquentProvider(array $config): IUserProvider
    {
        $hashDriver = $this->container['hash']->getDriver();

        return new EloquentUserProvider($hashDriver, $config['model']);
    }
}
