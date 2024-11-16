<?php

namespace Src\Main\Translation;

use Illuminate\Support\Arr;
use Src\Main\Translation\Loaders\ILoader;

class Translator implements ITranslator
{
    protected array $loaded = [];

    public function __construct(
        protected ILoader $loader
    ) {}

    public function has(string $key, string $language): bool
    {
        [$group, $newKey] = $this->parseKey($key);

        return $this->isContentLoaded($language, $group, $newKey) ||
            $this->get($key, $language) !== $key;
    }
    public function get(string $key, string $language): string
    {
        [$group, $newKey] = $this->parseKey($key);

        $result = $this->find($language, $group, $newKey);

        return empty($result) ? $key : $this->implodeResult($result);
    }
    protected function parseKey(string $key): array
    {
        $segments = explode(':', $key);

        if (count($segments) == 1) {
            return ["*", $segments[0]];
        }

        $item = $segments[1] ?? null;

        return [$segments[0], $item];
    }
    protected function find(string $language, string $group, string $key): array
    {
        $this->loadGroup($language, $group);

        return $this->getContent($language, $group, $key);
    }
    protected function loadGroup(string $language, string $group): void
    {
        if ($this->isGroupLoaded($language, $group)) {
            return;
        }

        $content = $this->loader->load($language, $group);

        $this->addContent($language, $group, $content);
    }
    protected function isGroupLoaded(string $language, string $group): bool
    {
        return Arr::has($this->loaded, "$language.$group");
    }
    protected function addContent(string $language, string $group, array $content): void
    {
        $this->loaded[$language][$group] = $content;
    }
    protected function getContent(string $language, string $group, string $key): array
    {
        if (!$this->isContentLoaded($language, $group, $key)) {
            return [];
        }

        $result = Arr::get($this->loaded[$language][$group], $key);

        return is_array($result) ? $result : [$result];
    }
    protected function isContentLoaded(string $language, string $group, string $key): bool
    {
        return Arr::has($this->loaded, "$language.$group.$key");
    }
    protected function implodeResult(array $items): ?string
    {
        return implode("-", $items);
    }
}
