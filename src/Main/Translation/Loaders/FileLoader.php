<?php

namespace Src\Main\Translation\Loaders;

use RuntimeException;
use Src\Main\Filesystem\Filesystem;

class FileLoader implements ILoader
{
    public function __construct(
        protected Filesystem $files,
        protected string $path
    ) {}
    public function load(string $language, string $group): array
    {
        if ($group == "*") {
            return $this->loadJson($language);
        }

        return $this->loadPath($language, $group);
    }
    protected function loadJson(string $language): array
    {
        $file = "{$this->path}/{$language}.json";

        if ($this->exists($file)) {
            $content = json_decode($this->files->get($file), true);

            if (!$content || json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Translation file {$file} contains an invalid JSON structure.");
            }

            return $content;
        }

        return [];
    }
    protected function loadPath(string $language, string $group): array
    {
        $file = "{$this->path}/{$language}/{$group}.php";

        if ($this->exists($file)) {
            return $this->files->getRequire($file);
        }

        return [];
    }
    protected function exists(string $file): bool
    {
        return $this->files->exists($file);
    }
}
