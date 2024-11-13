<?php

namespace Src\Main\View\Engines;

use Src\Main\Container\IContainer;
use Src\Main\Filesystem\Filesystem;
use Src\Main\View\Compilers\ICompilerFactory;
use Src\Main\View\Engines\Drivers\CompilerEngine;
use Src\Main\View\Engines\Drivers\FileEngine;
use Src\Main\View\Engines\Drivers\PhpEngine;

class EngineFactory implements IEngineFactory
{
    public function __construct(
        protected IContainer $container,
        protected ICompilerFactory $compilerFactory
    ) {}
    public function make(string $name): IEngine
    {
        $fileSystem = new FileSystem();

        return match ($name) {
            "php" => new PhpEngine($fileSystem),
            "file" => new FileEngine($fileSystem),
            default => new CompilerEngine($this->compilerFactory->make($name), $fileSystem),
        };
    }
}
