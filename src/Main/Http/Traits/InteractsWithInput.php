<?php

namespace Src\Main\Http\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Src\Symfony\Http\IRequestInput;
use stdClass;

trait InteractsWithInput
{
    public function isJson(): bool
    {
        return str_contains($this->header('CONTENT_TYPE') ?? '', 'json');
    }
    public function server(string $key): ?string
    {
        return $this->retrieveItem('server', $key);
    }
    public function hasHeader(string $key = null): bool
    {
        return ! is_null($this->header($key));
    }
    public function header(string $key): ?string
    {
        return $this->retrieveItem('headers', $key);
    }
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');

        $position = strrpos($header, 'Bearer ');

        if ($position !== false) {
            $header = substr($header, $position + 7);

            return str_contains($header, ',') ? strstr($header, ',', true) : $header;
        }

        return null;
    }
    public function exists(string $key): bool
    {
        return $this->has($key);
    }
    public function has(string ...$keys): bool
    {
        $input = $this->all();

        foreach ($keys as $value) {
            if (! Arr::has($input, $value)) {
                return false;
            }
        }

        return true;
    }
    public function hasAny(string ...$keys): bool
    {
        $input = $this->all();

        return Arr::hasAny($input, $keys);
    }
    public function missing(string ...$keys): bool
    {
        return ! $this->has(...$keys);
    }
    public function input(string $key = null, mixed  $default = null): mixed
    {
        return data_get(
            $this->getInputSource()->all() + $this->query->all(),
            $key,
            $default
        );
    }
    protected function isEmptyString(string $key): bool
    {
        $value = $this->input($key);

        return ! is_bool($value) && ! is_array($value) && trim((string) $value) === '';
    }
    public function keys(): array
    {
        return array_merge(array_keys($this->input()));
    }
    public function all(array $keys = []): array
    {
        $input = array_replace_recursive($this->input());

        if (! $keys) {
            return $input;
        }

        $results = [];

        foreach ($keys as $key) {
            Arr::set($results, $key, Arr::get($input, $key));
        }

        return $results;
    }
    public function boolean(string $key = null, bool $default = false): bool
    {
        return filter_var($this->input($key, $default), FILTER_VALIDATE_BOOLEAN);
    }
    public function integer(string $key, int $default = 0): int
    {
        return intval($this->input($key, $default));
    }
    public function float(string $key, float $default = 0.0): float
    {
        return floatval($this->input($key, $default));
    }
    public function collect(string ...$keys): Collection
    {
        return collect($this->only(...$keys));
    }
    public function only(string ...$keys): array
    {
        $results = [];

        $input = $this->all();

        $placeholder = new stdClass;

        foreach ($keys as $key) {
            $value = data_get($input, $key, $placeholder);

            if ($value !== $placeholder) {
                Arr::set($results, $key, $value);
            }
        }

        return $results;
    }
    public function except(string ...$keys): array
    {
        $results = $this->all();

        Arr::forget($results, $keys);

        return $results;
    }
    public function query(string $key): ?string
    {
        return $this->retrieveItem('query', $key);
    }
    public function post(string $key): ?string
    {
        return $this->retrieveItem('request', $key);
    }
    public function hasCookie(string $key): bool
    {
        return ! is_null($this->cookie($key));
    }
    public function cookie(string $key): ?string
    {
        return $this->retrieveItem('cookies', $key);
    }
    public function getItems(string $source): array
    {
        return $this->$source->all();
    }
    protected function retrieveItem(string $source, string $key, mixed $default = null): ?string
    {
        if ($this->$source instanceof IRequestInput) {
            return $this->$source->get($key, $default);
        }

        return null;
    }
}
