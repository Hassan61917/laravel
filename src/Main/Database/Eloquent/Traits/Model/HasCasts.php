<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use RuntimeException;
use Src\Main\Database\Eloquent\Casts\Json;
use Src\Main\Database\Exceptions\Eloquent\JsonEncodingException;
use Src\Main\Facade\Facades\Crypt;
use Src\Main\Facade\Facades\Hash;

trait HasCasts
{
    protected static array $castTypeCache = [];
    protected static array $primitiveCastTypes = [
        'bool',
        'boolean',
        'collection',
        'date',
        'datetime',
        'decimal',
        'double',
        'encrypted',
        'float',
        'hashed',
        'int',
        'integer',
        'json',
        'object',
        'real',
        'string',
        'timestamp',
    ];
    protected array $casts = [];
    public function getCasts(): array
    {
        if ($this->getIncrementing()) {
            return array_merge([$this->getKeyName() => $this->getKeyType()], $this->casts);
        }

        return $this->casts;
    }
    public function mergeCasts(array $casts): static
    {
        $this->casts = array_merge($this->casts, $casts);

        return $this;
    }
    protected function initializeHasCasts(): void
    {
        $casts = array_merge($this->casts, $this->casts());

        $this->casts = $casts;
    }
    protected function casts(): array
    {
        return [];
    }
    protected function hasCast(string $key, array $types = []): bool
    {
        if (!array_key_exists($key, $this->getCasts())) {
            return false;
        }

        $type = $this->getCastType($key);

        return empty($types) || in_array($type, $types);
    }
    protected function getCastType(string $key): ?string
    {
        return $this->getCasts()[$key] ?? null;
    }
    protected function toCast(string $key, mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if ($this->isDateAttribute($key)) {
            return $this->fromDateTime($value);
        }

        if ($this->isJsonCastable($key)) {
            return $this->castAttributeAsJson($key, $value);
        }

        if ($this->hasCast($key, ['hashed'])) {
            return $this->hashString($key, $value);
        }

        if ($this->isEncryptedCastable($key)) {
            return $this->encryptString($key, $value);
        }

        return $value;
    }
    protected function isDateAttribute(string $key): bool
    {
        return in_array($key, $this->getDates()) || $this->isDateCastable($key);
    }
    protected function isDateCastable(string $key): bool
    {
        return $this->hasCast($key, ['date', 'datetime']);
    }
    protected function getDates(): array
    {
        return $this->usesTimestamps() ? [
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ] : [];
    }
    protected function fromDateTime(mixed $value): string
    {
        return $this->asDateTime($value)->format($this->getDateFormat());
    }
    protected function asDateTime(mixed $value): Carbon
    {
        if (is_numeric($value)) {
            $value = Carbon::createFromTimestamp($value);
        }

        if (is_string($value)) {
            $value = Carbon::createFromFormat($this->getDateFormat(), $value);
        }

        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        return $value ?: Carbon::create();
    }
    protected function getDateFormat(): string
    {
        return $this->getConnection()->getQueryGrammar()->getDateFormat();
    }
    protected function isJsonCastable(string $key): bool
    {
        return $this->hasCast($key, ['array', 'json', 'object', 'collection']);
    }
    protected function castAttributeAsJson($key, $value): string
    {
        $value = $this->asJson($value);

        if (!$value) {
            throw JsonEncodingException::forAttribute($this, $key, json_last_error_msg());
        }

        return $value;
    }
    protected function asJson(mixed $value): string
    {
        return Json::encode($value);
    }
    protected function fromJson(string $value, bool $asObject = false): mixed
    {
        return Json::decode($value, ! $asObject);
    }
    protected function hashString(string $key, string $value): string
    {
        if (! Hash::isHashed($value)) {
            return Hash::make($value);
        }

        if (! Hash::verifyConfiguration($value)) {
            throw new RuntimeException("Could not verify the hashed value's configuration.");
        }

        return $value;
    }
    protected function isEncryptedCastable(string $key): bool
    {
        return $this->hasCast($key, ['encrypted']);
    }
    protected function encryptString(string $key, string $value): string
    {
        return Crypt::encrypt($value, false);
    }
    protected function decryptString(string $key, string $value): string
    {
        return Crypt::decrypt($value, false);
    }
    protected function castAttribute(string $key, mixed $value): mixed
    {
        $castType = $this->getCastType($key);

        if (is_null($value)) {
            return null;
        }

        if ($this->isEncryptedCastable($key)) {
            return $this->decryptString($key, $value);
        }

        return match ($castType) {
            'int', 'integer' => (int) $value,
            'real', 'float', 'double' => $this->fromFloat($value),
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'object' => $this->fromJson($value, true),
            'array', 'json' => $this->fromJson($value),
            'datetime' => $this->asDateTime($value),
            'timestamp' => $this->asTimestamp($value),
            default => $value,
        };
    }
    protected function fromFloat(mixed $value): float
    {
        return match ((string) $value) {
            'Infinity' => INF,
            '-Infinity' => -INF,
            'NaN' => NAN,
            default => (float) $value,
        };
    }
    protected function asTimestamp(mixed $value): int
    {
        return $this->asDateTime($value)->getTimestamp();
    }
}
