<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

trait HandleBoot
{
    protected static array $booted = [];
    protected static array $traitInitializers = [];
    public static function clearBootedModels(): void
    {
        static::$booted = [];

        static::clearGlobalScopes();
    }
    protected static function booting() {}
    protected static function boot(): void
    {
        static::bootTraits();
    }
    protected static function booted()
    {
        //
    }
    protected static function bootTraits(): void
    {
        $class = static::class;

        $booted = [];

        static::$traitInitializers[$class] = [];

        foreach (class_uses_recursive($class) as $trait) {
            $name = class_basename($trait);

            $method = 'boot' . $name;

            if (method_exists($class, $method) && !in_array($method, $booted)) {
                forward_static_call([$class, $method]);

                $booted[] = $method;
            }

            $method = 'initialize' . $name;

            if (method_exists($class, $method)) {
                static::$traitInitializers[$class][] = $method;
                static::$traitInitializers[$class] = array_unique(static::$traitInitializers[$class]);
            }
        }
    }
    protected function bootIfNotBooted(): void
    {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            $this->fireModelEvent('booting');

            static::booting();
            static::boot();
            static::booted();

            $this->fireModelEvent('booted');
        }

        $this->initializeTraits();
    }
    protected function initializeTraits(): void
    {
        foreach (static::$traitInitializers[static::class] as $method) {
            $this->{$method}();
        }
    }
}
