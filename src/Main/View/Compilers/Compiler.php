<?php

namespace Src\Main\View\Compilers;

use ErrorException;
use Src\Main\Filesystem\Filesystem;

abstract class Compiler implements ICompiler
{
    protected bool $shouldBeCached = true;
    public function __construct(
        protected Filesystem $files,
        protected string $cachePath
    ) {}
    public function getCompiledPath(string $path): string
    {
        $hash = hash("md5", $path) . ".php";

        return "{$this->cachePath}/{$hash}";
    }
    public function isExpired(string $path): bool
    {
        $compiled = $this->getCompiledPath($path);

        if (! $this->shouldBeCached || !$this->exists($compiled)) {
            return true;
        }

        try {
            return $this->lastModified($path) >= $this->lastModified($compiled);
        } catch (ErrorException $exception) {
            if (!$this->exists($compiled)) {
                return true;
            }

            throw $exception;
        }
    }
    protected function lastModified(string $path): int
    {
        return $this->files->lastModified($path);
    }
    protected function read(string $path): ?string
    {
        return $this->files->get($path);
    }
    protected function createDirectory(string $path): void
    {
        $dir = dirname($path);

        $this->files->makeDirectory($dir, 0777, true, true);
    }
    protected function write(string $path, string $newContent): void
    {
        if (!$this->isDirectoryExists($path)) {
            $this->createDirectory($path);
        }

        $this->files->put($path, $newContent);
    }
    protected function isDirectoryExists(string $path): bool
    {
        return $this->exists(dirname($path));
    }
    protected function exists(string $path): bool
    {
        return $this->files->exists($path);
    }
}
