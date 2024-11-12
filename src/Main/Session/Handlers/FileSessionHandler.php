<?php

namespace Src\Main\Session\Handlers;

use Carbon\Carbon;
use Src\Main\Filesystem\Filesystem;
use Src\Symfony\Finder\Finder;

class FileSessionHandler implements \SessionHandlerInterface
{
    public function __construct(
        protected Filesystem $files,
        protected string $path,
        protected int $minutes
    ) {
        $this->createPath();
    }
    public function open(string $path, string $name): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }
    public function write(string $id, string $data): bool
    {
        $path = $this->getPath($id);

        $this->files->put($path, $data);

        return true;
    }
    public function destroy(string $id): bool
    {
        $path = $this->getPath($id);

        $this->files->delete($path);

        return true;
    }
    public function read(string $id): string
    {
        $path = $this->getPath($id);

        if (
            $this->files->isFile($path) &&
            $this->files->lastModified($path) >= Carbon::now()->subMinutes($this->minutes)->getTimestamp()
        ) {
            return $this->files->sharedGet($path);
        }

        return '';
    }
    public function gc(int $max_lifetime): int
    {
        $files = Finder::create()
            ->in($this->path)
            ->date("<=", time() - $max_lifetime);

        $deletedSessions = 0;

        foreach ($files as $file) {
            $this->files->delete($file->getRealPath());
            $deletedSessions++;
        }

        return $deletedSessions;
    }
    protected function getPath(string $id): string
    {
        return $this->path . '/' . $id;
    }
    protected function createPath(): void
    {
        $this->files->ensureDirectoryExists($this->path);
    }
}
