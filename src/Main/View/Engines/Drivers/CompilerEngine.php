<?php

namespace Src\Main\View\Engines\Drivers;

use Src\Main\Filesystem\Filesystem;
use Src\Main\Http\Exceptions\HttpException;
use Src\Main\View\Compilers\ICompiler;
use Src\Main\View\ViewException;
use Throwable;

class CompilerEngine extends PhpEngine
{
    protected array $lastCompiled = [];
    protected array $compiled = [];
    public function __construct(
        protected ICompiler $compiler,
        Filesystem $files
    ) {
        parent::__construct($files);
    }
    public function clearCompiled(): void
    {
        $this->compiled = [];
    }
    public function get(string $path, array $data = []): string
    {
        $this->lastCompiled[] = $path;

        if (!$this->isCompiled($path) && $this->isExpired($path)) {
            $this->compiler->compile($path);
        }

        $results = $this->evaluate($path, $data);

        $this->compiled[$path] = true;

        array_pop($this->lastCompiled);

        return $results;
    }
    protected function handleViewException(Throwable $e): void
    {
        if ($e instanceof HttpException) {
            parent::handleViewException($e);
        }

        $e = new ViewException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);

        parent::handleViewException($e);
    }
    protected function getMessage(Throwable $e): string
    {
        return $e->getMessage() . ' (View: ' . realpath(end($this->lastCompiled)) . ')';
    }
    protected function isExpired(string $path): bool
    {
        return $this->compiler->isExpired($path);
    }
    protected function isCompiled(string $path): bool
    {
        return isset($this->compiled[$path]);
    }
    protected function evaluate(string $path, array $data): string
    {
        return $this->evaluatePath($this->compiler->getCompiledPath($path), $data);
    }
}
