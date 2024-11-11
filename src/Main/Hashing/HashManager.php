<?php

namespace Src\Main\Hashing;

use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;
use Src\Main\Hashing\Drivers\IHashDriver;
use Src\Main\Hashing\Drivers\IHashDriverFactory;

class HashManager extends DriverManager
{
    protected string $configName = "hashing";
    public function __construct(
        IContainer $container,
        protected IHashDriverFactory $driverFactory,
    ) {
        parent::__construct($container);
    }
    public function info(string $hash): array
    {
        return $this->getDriver()->info($hash);
    }
    public function make(string $value, array $options = []): string
    {
        return $this->getDriver()->make($value, $options);
    }
    public function check(string $value, string $hash, array $options = []): bool
    {
        return $this->getDriver()->check($value, $hash, $options);
    }
    public function needsRehash(string $hash, array $options = []): bool
    {
        return $this->getDriver()->needsRehash($hash, $options);
    }
    public function isHashed(string $value): bool
    {
        return password_get_info($value)['algo'] !== null;
    }
    protected function create(string $driver): IHashDriver
    {
        $config = $this->getConfig($driver);

        return $this->driverFactory->make($driver, $config);
    }
}
