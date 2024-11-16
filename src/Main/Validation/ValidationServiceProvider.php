<?php

namespace Src\Main\Validation;

use Src\Main\Support\ServiceProvider;
use Src\Main\Validation\Rules\GlobalRules;

class ValidationServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "validator" => ["validator" => ValidatorManager::class]
        ];
    }
    public function register(): void
    {
        $this->registerValidatorFactory();
        $this->registerMessageHandler();
        $this->registerValidator();
    }
    protected function registerValidatorFactory(): void
    {
        $this->app->singleton("globalRules", GlobalRules::class);
        $this->app->singleton(IValidatorFactory::class, ValidatorFactory::class);
    }
    protected function registerMessageHandler(): void
    {
        $this->app->singleton(IMessageFormatter::class, MessageFormatter::class);
        $this->app->singleton(IMessageHandler::class, MessageHandler::class);
    }
    protected function registerValidator(): void
    {
        $this->app->singleton("validator", fn($app) => new ValidatorManager($app[IValidatorFactory::class]));
    }
}
