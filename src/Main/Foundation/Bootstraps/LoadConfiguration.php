<?php

namespace Src\Main\Foundation\Bootstraps;

use Src\Main\Config\ConfigRepository;
use Src\Main\Foundation\Application;
use Src\Symfony\Finder\Finder;

class LoadConfiguration implements IBootstrap
{
    public function bootstrap(Application $app): void
    {
        $config = new ConfigRepository();

        $this->loadConfig($app, $config);

        $app->instance("config", $config);
    }
    protected function loadConfig(Application $app, ConfigRepository $repository): void
    {
        $appConfig = $this->getAppConfig($app);

        $baseConfig = $this->getBaseConfig($app);

        $this->mergeConfigs($repository, $appConfig, $baseConfig);
    }
    protected function getAppConfig(Application $app): array
    {
        return $this->getConfig($app->configPath());
    }
    protected function getBaseConfig(Application $app): array
    {
        $path = $app->basePath("src\\Config");
        return $this->getConfig($path);
    }
    protected function getConfig(string $path): array
    {
        $files = [];

        $path = realpath($path);

        if (!$path) {
            return [];
        }

        foreach (Finder::create()->in($path)->name(".php") as $file) {
            $dir = $this->getDir($file->getPath(), $path);
            $name = substr($file->getFilename(), 0, -4);
            $files[$dir . $name] = require_once $file;
        }

        return $files;
    }
    protected function mergeConfigs(ConfigRepository $repository, array ...$configs): void
    {
        foreach ($configs as $config) {
            foreach ($config as $name => $items) {
                if ($repository->has($name)) {
                    $items = array_merge($items, $repository->get($name));
                }
                $repository->set($name, $items);
            }
        }
    }
    protected function getDir(string $path, string $configPath): string
    {
        $result = trim(str_replace($configPath, '', $path), DIRECTORY_SEPARATOR);
        if ($result != "") {
            $result = $result . "/";
        }
        return $result;
    }
}
