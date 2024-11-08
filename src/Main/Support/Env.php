<?php

namespace Src\Main\Support;

use Dotenv\Repository\Adapter\PutenvAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use PhpOption\Option;
use PhpOption\Some;
use RuntimeException;

class Env
{
    protected static bool $putEnv = true;
    protected static ?RepositoryInterface $repository = null;
    public static function enablePutEnv(): void
    {
        self::$putEnv = true;
        self::$repository = null;
    }
    public static function disablePutEnv(): void
    {
        static::$putEnv = false;
        static::$repository = null;
    }
    public static function getRepository(): ?RepositoryInterface
    {
        if (is_null(static::$repository)) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();

            if (static::$putEnv) {
                $builder = $builder->addAdapter(PutenvAdapter::class);
            }

            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getOption($key)->getOrCall(fn() => value($default));
    }
    public static function getOrFail(string $key)
    {
        return self::getOption($key)->getOrThrow(new RuntimeException("Environment variable [$key] has no value."));
    }
    protected static function getOption(string $key): Some|Option
    {
        return Option::fromValue(static::getRepository()->get($key))
            ->map(function ($value) {
                switch (strtolower($value)) {
                    case 'true':
                    case '(true)':
                        return true;
                    case 'false':
                    case '(false)':
                        return false;
                    case 'empty':
                    case '(empty)':
                        return '';
                    case 'null':
                    case '(null)':
                        return null;
                }

                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
            });
    }
}
