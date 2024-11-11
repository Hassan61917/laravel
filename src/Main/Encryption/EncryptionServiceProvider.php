<?php

namespace Src\Main\Encryption;

use RuntimeException;
use Src\Main\Support\ServiceProvider;
use Src\Main\Utils\Str;

class EncryptionServiceProvider extends ServiceProvider
{
    public function getAliases(): array
    {
        return [
            "encryptor" => [Encryptor::class, IEncryptor::class]
        ];
    }
    public function register(): void
    {
        $this->registerEncryptor();
    }
    protected function registerEncryptor(): void
    {
        $this->app->singleton("encryptor", function ($app) {
            $config = $app["config"]["app"];
            $key = $this->parseKey($config['key']);
            $cipher = $config['cipher'];
            return new Encryptor($key, $cipher);
        });
    }
    protected function parseKey(?string $key): string
    {
        if (is_null($key)) {
            throw new RuntimeException('No application encryption key has been specified.');
        }

        $prefix = 'base64:';

        if (str_starts_with($key, $prefix)) {
            $key = base64_decode(Str::after($key, $prefix));
        }

        return $key;
    }
}
