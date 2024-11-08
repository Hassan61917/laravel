<?php

namespace Src\Symfony\Finder\Handlers;

use SplFileInfo;

abstract class FilterHandler implements IHandler, \Iterator
{
    protected ?SplFileInfo $currenFile;
    public function __construct(
        protected \Iterator $iterator,
    ) {}
    public function handle(): \Iterator
    {
        return $this;
    }
    public function current(): mixed
    {
        return $this->currenFile;
    }
    public function valid(): bool
    {
        $this->currenFile = $this->findNext();
        return !is_null($this->currenFile);
    }
    public function next(): void
    {
        $this->iterator->next();
    }
    public function key(): mixed
    {
        return $this->iterator->key();
    }
    public function rewind(): void
    {
        $this->iterator->rewind();
    }
    protected abstract function accept(SplFileInfo $file): bool;
    protected function findNext(): ?SplFileInfo
    {
        while ($this->iterator->valid()) {
            $file = $this->iterator->current();
            if ($this->accept($file)) {
                return $file;
            }
            $this->iterator->next();
        }
        return null;
    }
}
