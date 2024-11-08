<?php

namespace Src\Symfony\Finder\Handlers;

class ExcludeHandler extends FilterHandler
{
    public function __construct(
        \Iterator $iterator,
        protected array $exclude = []
    ) {
        parent::__construct($iterator);
    }
    protected function accept(\SplFileInfo $file): bool
    {
        foreach ($this->exclude as $exclude) {
            $exclude = $exclude[0] == "/" ? $exclude : "/$exclude";
            if (str_contains($file->getPath(), "$exclude")) {
                return false;
            }
        }
        return true;
    }
}
