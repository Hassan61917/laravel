<?php

namespace Src\Symfony\Finder\Handlers;

use SplFileInfo;

class RecursiveHandler implements IHandler, \Iterator
{
    protected int $depth = 0;
    protected bool $started = false;
    protected ?SplFileInfo $currentFile = null;
    protected array $map = [];
    public function __construct(
        protected string $path,
        protected int $minDepth = 0,
        protected int $maxDepth = PHP_INT_MAX
    ) {}
    public function getDepth(): int
    {
        return $this->depth;
    }
    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }
    public function current(): mixed
    {
        return $this->currentFile;
    }
    public function next(): void
    {
        $this->currentFile = $this->findNext($this->path, 1);
    }
    public function setMaxDepth(int $maxDepth): void
    {
        $this->maxDepth = $maxDepth;
    }
    public function key(): mixed
    {
        return $this->depth;
    }
    public function valid(): bool
    {
        $this->start();
        return  !is_null($this->currentFile);
    }
    public function rewind(): void
    {
        // TODO: Implement rewind() method.
    }
    public function handle(): \Iterator
    {
        return $this;
    }
    private function findNext(string $path, int $depth): ?SplFileInfo
    {
        if ($this->depth > $this->maxDepth) {
            return null;
        }
        $files = $this->getFiles($path);
        $result = null;
        foreach ($files as $file) {
            if ($result) {
                break;
            }
            if (is_file($file) && !array_key_exists($file, $this->map) && $this->depth >= $this->minDepth) {
                $this->map[$file] = true;
                $this->depth = max($this->depth, $depth);
                $result = new SplFileInfo($file);
            } elseif (is_dir($file)) {
                $result = $this->findNext($file, $depth + 1);
            }
        }
        return $result;
    }
    private function getFiles(string $path): array
    {
        $files = scandir($path);
        $files = array_splice($files, 2);
        return array_map(fn($file) => "{$path}/{$file}", $files);
    }
    private function start(): void
    {
        if ($this->started) {
            return;
        }
        $this->next();
        $this->started = true;
    }
}
