<?php

namespace Src\Main\View\Engines\Drivers;

use Src\Main\Filesystem\Filesystem;
use Src\Main\View\Engines\IEngine;

class FileEngine implements IEngine
{
    public function __construct(
        protected Filesystem $files
    ) {}
    public function get(string $path, array $data = []): string
    {
        return $this->files->get($path);
    }
}
