<?php

namespace Src\Main\Translation;

use Src\Main\Filesystem\Filesystem;
use Src\Main\Support\ServiceProvider;
use Src\Main\Translation\Loaders\FileLoader;
use Src\Main\Translation\Loaders\ILoader;

class TranslationServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "translator" => [ITranslator::class, Translator::class]
        ];
    }
    public function register(): void
    {
        $this->registerLoader();
        $this->registerTranslator();
    }
    protected function registerLoader(): void
    {
        $this->app->singleton(ILoader::class, function ($app) {
            $files = new Filesystem();
            $path = $app->basePath('languages');
            return new FileLoader($files, $path);
        });
    }
    protected function registerTranslator(): void
    {
        $this->app->singleton("translator", function ($app) {
            return new Translator($app[ILoader::class]);
        });
    }
}
