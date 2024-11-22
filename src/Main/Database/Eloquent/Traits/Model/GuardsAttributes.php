<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

trait GuardsAttributes
{
    protected static bool $unguarded = false;
    protected array $fillable = [];
    protected array $guarded = ["*"];
    public static function unguard(): void
    {
        static::$unguarded = true;
    }
    public static function reguard(): void
    {
        static::$unguarded = false;
    }
    public static function isUnguarded(): bool
    {
        return static::$unguarded;
    }
    public static function unguarded(callable $callback)
    {
        if (self::isUnguarded()) {
            return $callback();
        }

        static::unguard();

        try {
            return $callback();
        } finally {
            static::reguard();
        }
    }
    public function getFillable(): array
    {
        return $this->fillable;
    }
    public function getGuarded(): array
    {
        return $this->guarded;
    }
    public function guardAll(): bool
    {
        return empty($this->getFillable()) && $this->getGuarded() == ['*'];
    }
    public function isFillable(string $key): bool
    {
        if (self::isUnguarded() || in_array($key, $this->getFillable())) {
            return true;
        }

        if ($this->isGuarded($key)) {
            return false;
        }

        return empty($this->getFillable()) &&
            ! str_contains($key, '.') &&
            ! str_starts_with($key, '_');
    }
    public function isGuarded(string $key): bool
    {
        if (empty($this->getGuarded())) {
            return false;
        }

        return $this->getGuarded() == ['*'] || ! in_array($key, $this->getGuarded());
    }
    protected function fillableFromArray(array $attributes): array
    {
        if ($this->guardAll()) {
            return [];
        }

        if (count($this->getFillable()) && !self::isUnguarded()) {

            $fillables = array_flip($this->getFillable());

            return array_intersect_key($attributes, $fillables);
        }

        return $attributes;
    }
}
