<?php

namespace Src\Main\View\Compilers;

interface ICompilerFactory
{
    public function make(string $name): ICompiler;
}
