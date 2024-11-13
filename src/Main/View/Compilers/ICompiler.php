<?php

namespace Src\Main\View\Compilers;

interface ICompiler
{
    public function getCompiledPath(string $path): string;
    public function isExpired(string $path): bool;
    public function compile(string $path): void;
}
