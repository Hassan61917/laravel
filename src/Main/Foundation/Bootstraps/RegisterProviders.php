<?php

namespace Src\Main\Foundation\Bootstraps;

use Src\Main\Foundation\Application;

class RegisterProviders implements IBootstrap
{
    protected static array $merge = [];
    protected static string $bootstrapProviderPath;
    public static function setProviderPath(string $bootstrapProviderPath): void
    {
        self::$bootstrapProviderPath = $bootstrapProviderPath;
    }
    public function bootstrap(Application $app): void
    {
        $this->mergeProviders($app);

        $app->registerProviders();
    }
    protected function mergeProviders(Application $app): void
    {
        $providers = $this->filterProviders();

        $providers = array_merge(
            $app["config"]["app.providers"],
            array_values($providers),
            self::$merge,
        );

        $app["config"]["app.providers"] = $providers;
    }
    protected function filterProviders(): array
    {
        $path = self::$bootstrapProviderPath;

        if ($path && file_exists($path)) {
            $providers = require_once $path;

            return array_filter($providers, fn($provider) => class_exists($provider));
        }
        return [];
    }
}
