<?php

namespace Src\Main\Foundation\Console\Traits;

trait FileHelper
{
    protected function exists(string $path): bool
    {
        return $this->files->exists($path);
    }
    protected function makeDirectory(string $path): void
    {
        $path = dirname($path);

        $this->files->ensureDirectoryExists($path, 0777);
    }
    protected function put(string $filePath, string $name): void
    {
        $this->files->put($filePath, $this->buildClass($name));
    }
    protected function read(string $path): ?string
    {
        return $this->files->get($path);
    }
}
