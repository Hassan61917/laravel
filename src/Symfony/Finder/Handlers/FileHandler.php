<?php

namespace Src\Symfony\Finder\Handlers;

use Iterator;
use SplFileInfo;

class FileHandler extends FilterHandler
{
    public function __construct(
        Iterator $iterator,
        protected array $names
    ) {
        parent::__construct($iterator);
    }
    protected function accept(SplFileInfo $file): bool
    {
        foreach ($this->names as $name) {
            if (str_ends_with($file->getFilename(), $name)) {
                return true;
            }
        }
        return false;
    }
}
