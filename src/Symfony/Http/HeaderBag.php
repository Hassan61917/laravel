<?php

namespace Src\Symfony\Http;

use ArrayIterator;
use Countable;
use DateTimeImmutable;
use IteratorAggregate;
use RuntimeException;
use Stringable;

class HeaderBag implements IteratorAggregate, Countable, Stringable
{
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';
    protected array $headers = [];
    protected array $cacheControl = [];
    protected function __construct(
        array $headers = []
    ) {
        $this->add($headers);
    }
    public function set(string $key, string|array $values, bool $replace = true): void
    {
        $key = $this->getKey($key);

        if (is_string($values)) {
            $values = [$values];
        }

        $values = array_values($values);

        if ($replace || !$this->hasHeader($key)) {
            $this->headers[$key] = $values;
        } else {
            $this->headers[$key] = array_merge($this->headers[$key], $values);
        }

        if ($key === 'cache-control') {
            $this->cacheControl = $this->parseCacheControl(implode(', ', $this->headers[$key]));
        }
    }
    public function all(?string $key = null): array
    {
        if ($key) {
            return $this->headers[$this->getKey($key)] ?? [];
        }

        return $this->headers;
    }
    public function keys(): array
    {
        return array_keys($this->all());
    }
    public function replace(array $headers = []): void
    {
        $this->headers = [];
        $this->add($headers);
    }
    public function add(array $headers): void
    {
        foreach ($headers as $key => $values) {
            $this->set($key, $values);
        }
    }
    public function get(string $key, ?string $default = null): ?string
    {
        $headers = $this->all($key);

        if (!$headers) {
            return $default;
        }

        if (is_null($headers[0])) {
            return null;
        }

        return (string) $headers[0];
    }
    public function has(string $key): bool
    {
        return array_key_exists($this->getKey($key), $this->all());
    }
    public function contains(string $key, string $value): bool
    {
        return in_array($value, $this->all($key));
    }
    public function remove(string $key): void
    {
        $key = strtr($key, self::UPPER, self::LOWER);

        unset($this->headers[$key]);

        if ($key === "cache-control") {
            $this->cacheControl = [];
        }
    }
    public function getDate(string $key, ?\DateTimeInterface $default = null): ?DateTimeImmutable
    {
        $value = $this->get($key);

        if (is_null($value)) {
            return  $default ? DateTimeImmutable::createFromInterface($default) : null;
        }

        $date = DateTimeImmutable::createFromFormat(\DATE_RFC2822, $value);

        if (!$date) {
            throw new RuntimeException("The {$key} HTTP header is not parseable {$value}.");
        }

        return $date;
    }
    public function addCacheControlDirective(string $key, bool|string $value = true): void
    {
        $this->cacheControl[$key] = $value;

        $this->set('Cache-Control', $this->getCacheControlHeader());
    }
    public function hasCacheControlDirective(string $key): bool
    {
        return array_key_exists($key, $this->cacheControl);
    }
    public function getCacheControlDirective(string $key): bool|string|null
    {
        return $this->cacheControl[$key] ?? null;
    }
    public function removeCacheControlDirective(string $key): void
    {
        unset($this->cacheControl[$key]);

        $this->set('Cache-Control', $this->getCacheControlHeader());
    }
    public function getIterator(): ArrayIterator
    {
        return new \ArrayIterator($this->headers);
    }
    public function count(): int
    {
        return count($this->headers);
    }
    public function __toString(): string
    {
        $headers = $this->all();

        if (!$headers) {
            return '';
        }

        ksort($headers);

        $max = max(array_map('strlen', array_keys($headers))) + 1;

        $content = '';
        foreach ($headers as $name => $values) {
            $name = ucwords($name, '-');
            foreach ($values as $value) {
                $content .= sprintf("%-{$max}s %s\r\n", $name . ':', $value);
            }
        }

        return $content;
    }
    public function getKey(string $key): string
    {
        return strtr($key, self::UPPER, self::LOWER);
    }
    protected function getCacheControlHeader(): string
    {
        ksort($this->cacheControl);

        return HeaderUtils::toString($this->cacheControl, ',');
    }
    protected function parseCacheControl(string $header): array
    {
        $parts = HeaderUtils::split($header, ',=');

        return HeaderUtils::combine($parts);
    }
    protected function hasHeader(string $key): bool
    {
        return isset($this->headers[$key]);
    }
}
