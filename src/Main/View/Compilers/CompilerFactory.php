<?php

namespace Src\Main\View\Compilers;

use Src\Main\Container\IContainer;
use Src\Main\Filesystem\Filesystem;
use Src\Main\View\Compilers\Custom\CustomCompiler;

class CompilerFactory implements ICompilerFactory
{
    public function __construct(
        protected IContainer $app
    ) {}
    public function make(string $name): ICompiler
    {
        return new CustomCompiler(
            new Filesystem(),
            $this->app["config"]["view.compiled"]
        );
    }
}
