<?php

namespace Src\Main\View;

use Closure;
use InvalidArgumentException;
use Src\Main\Container\IContainer;
use Src\Main\View\Engines\EngineManager;
use Src\Main\View\Engines\IEngine;
use Src\Main\View\Finders\IFinder;

class ViewManager implements IViewFactory
{
    protected array $shared = [];
    protected array $compileExtensions = [];
    protected array $extensions = [
        '.custom.php' => 'custom',
        '.php' => 'php',
        '.css' => 'file',
        '.html' => 'file',
    ];
    public function __construct(
        protected IFinder $viewFinder,
        protected EngineManager $engineManager,
        protected IContainer $container
    ) {}
    public function make(string $name, array $data = []): View
    {
        $path = $this->resolvePath($name);

        return $this->createView($path, $name, $data);
    }
    public function file(string $path, array $data = []): View
    {
        return $this->createView($path, $path, $data);
    }
    public function exists(string $name): bool
    {
        try {
            $this->resolvePath($name);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
    public function share(string $key, mixed $value = null): static
    {
        $this->shared[$key] = $value;

        return $this;
    }
    public function getShared(): array
    {
        return $this->shared;
    }
    public function shared(string $key, mixed $default = null): mixed
    {
        return $this->shared[$key] ?? $default;
    }
    public function getExtensions(): array
    {
        return array_merge($this->compileExtensions, $this->extensions);
    }
    public function addPath(string ...$paths): static
    {
        foreach ($paths as $path) {
            $this->container["config"]->push("view.paths", $path);
        }

        return $this;
    }
    public function addExtension(string $extension, string $engine, Closure $resolver = null): void
    {
        $extension = strtolower($extension);

        $extension = $extension[0] == '.' ? $extension : '.' . $extension;

        if (isset($resolver)) {
            $this->engineManager->extend($engine, $resolver);
        }

        $this->extensions[$extension] = $engine;

        $this->compileExtensions[$extension] = $engine;
    }
    protected function resolvePath(string $name): string
    {
        return $this->viewFinder->find($name, array_keys($this->extensions));
    }
    protected function createView(string $path, string $name, array $data): View
    {
        $view = new View($path, $name, $data);

        $view->setViewManager($this)
            ->setEngine($this->resolveEngine($path));

        return $view;
    }
    protected function resolveEngine(string $path): IEngine
    {
        $extension = $this->findExtension($path);

        if (!$extension) {
            throw new InvalidArgumentException("Extension '{$extension}' not found");
        }

        $engine = $this->getExtensions()[$extension];

        return $this->engineManager->getDriver($engine);
    }
    protected function findExtension(string $path): ?string
    {
        $extensions = array_keys($this->getExtensions());

        foreach ($extensions as $extension) {
            if (str_ends_with($path, $extension)) {
                return $extension;
            }
        }

        return null;
    }
}
