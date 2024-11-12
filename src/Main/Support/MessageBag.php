<?php

namespace Src\Main\Support;

use Illuminate\Support\Arr;

class MessageBag
{
    protected array $messages = [];
    protected string $format = ':message';
    public function __construct(array $messages = [])
    {
        foreach ($messages as $key => $value) {
            $this->messages[$key] = array_unique($value);
        }
    }
    public function keys(): array
    {
        return array_keys($this->messages);
    }
    public function add(string $key, string $message): static
    {
        if ($this->isUnique($key, $message)) {
            $this->messages[$key][] = $message;
        }

        return $this;
    }
    public function addIf(bool $boolean, string $key, string $message): static
    {
        return $boolean ? $this->add($key, $message) : $this;
    }
    public function merge(array $messages): static
    {
        $this->messages = array_merge_recursive($this->messages, $messages);

        return $this;
    }
    protected function isUnique(string $key, string $message): bool
    {
        return ! isset($messages[$key]) || ! in_array($message, $messages[$key]);
    }
    public function has(string ...$keys): bool
    {
        if ($this->isEmpty()) {
            return false;
        }

        if (empty($keys)) {
            return $this->any();
        }

        foreach ($keys as $key) {
            if ($this->first($key) === '') {
                return false;
            }
        }

        return true;
    }
    public function hasAny(string ...$keys): bool
    {
        if ($this->isEmpty()) {
            return false;
        }
        foreach ($keys as $key) {
            if ($this->has($key)) {
                return true;
            }
        }

        return false;
    }
    public function missing(string ...$keys): bool
    {
        return ! $this->hasAny(...$keys);
    }
    public function first(string $key = null, string $format = null)
    {
        $messages = is_null($key) ? $this->all($format) : $this->get($key, $format);

        $firstMessage = Arr::first($messages, null, '');

        return is_array($firstMessage) ? Arr::first($firstMessage) : $firstMessage;
    }
    public function get(string $key, string $format = null): array
    {
        if (array_key_exists($key, $this->messages)) {
            return $this->transform(
                $this->messages[$key],
                $this->checkFormat($format),
                $key
            );
        }

        if (str_contains($key, '*')) {
            return $this->getMessagesForWildcardKey($key, $format);
        }

        return [];
    }
    protected function getMessagesForWildcardKey(string $key, string $format = null): array
    {
        $messages = array_filter($this->messages, fn($messages, $messageKey) => $key == $messageKey);
        return array_map(
            fn($messages, $messageKey) =>
            $this->transform($messages, $this->checkFormat($format), $messageKey),
            $messages
        );
    }
    public function all(string $format = null): array
    {
        $format = $this->checkFormat($format);

        $all = [];

        foreach ($this->messages as $key => $messages) {
            $all = array_merge($all, $this->transform($messages, $format, $key));
        }

        return $all;
    }
    public function unique(string $format = null): array
    {
        return array_unique($this->all($format));
    }
    public function forget(string $key): static
    {
        unset($this->messages[$key]);

        return $this;
    }
    protected function transform(array $messages, string $format, string $messageKey): array
    {
        if ($format == ':message') {
            return  $messages;
        }
        return array_map(fn($message) => str_replace([':message', ':key'], [$message, $messageKey], $format), $messages);
    }
    protected function checkFormat(string $format): string
    {
        return $format ?: $this->format;
    }
    public function messages(): array
    {
        return $this->messages;
    }
    public function getMessages(): array
    {
        return $this->messages();
    }
    public function getFormat(): string
    {
        return $this->format;
    }
    public function setFormat(string $format = ':message'): static
    {
        $this->format = $format;

        return $this;
    }
    public function isEmpty(): bool
    {
        return ! $this->any();
    }
    public function isNotEmpty(): bool
    {
        return $this->any();
    }
    public function any(): bool
    {
        return $this->count() > 0;
    }
    public function count(): int
    {
        return count($this->messages, COUNT_RECURSIVE) - count($this->messages);
    }
    public function toArray(): array
    {
        return $this->getMessages();
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
    public function __toString(): string
    {
        return $this->toJson();
    }
}
