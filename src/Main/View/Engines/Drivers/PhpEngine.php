<?php

namespace Src\Main\View\Engines\Drivers;

use Src\Main\Filesystem\Filesystem;
use Src\Main\View\Engines\IEngine;
use Throwable;

class PhpEngine implements IEngine
{
    public function __construct(
        protected Filesystem $files
    ) {}
    public function get(string $path, array $data = []): string
    {
        return $this->evaluatePath($path, $data);
    }
    protected function evaluatePath(string $path, array $data = []): string
    {
        try {
            $this->files->getRequire($path, $data);
        } catch (Throwable $e) {
            $this->handleViewException($e);
        }

        return ltrim(ob_get_clean());
    }
    protected function handleViewException(Throwable $e): void
    {
        throw $e;
    }
}
