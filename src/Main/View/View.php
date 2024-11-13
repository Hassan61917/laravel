<?php

namespace Src\Main\View;

use Src\Main\Http\Response;
use Src\Main\Routing\Route\IActionResult;
use Src\Main\Support\Htmlable;
use Src\Main\View\Engines\IEngine;
use Stringable;

class View implements IView, IActionResult, Stringable, Htmlable
{
    protected ViewManager $viewManager;
    protected IEngine $engine;
    public function __construct(
        protected string $path,
        protected string $name,
        protected array $data = []
    ) {}
    public function setViewManager(ViewManager $viewManager): static
    {
        $this->viewManager = $viewManager;

        return $this;
    }
    public function setEngine(IEngine $engine): static
    {
        $this->engine = $engine;

        return $this;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function with(string $key, mixed $value = null): static
    {
        $this->data[$key] = $value;

        return $this;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function render(): string
    {
        return $this->getContents();
    }
    public function toHtml(): string
    {
        return $this->render();
    }
    public function get(): Response
    {
        return new Response($this->render());
    }
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset];
    }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->with($offset, $value);
    }
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }
    protected function getContents(): string
    {
        return $this->engine->get($this->path, $this->gatherData());
    }
    protected function gatherData(): array
    {
        return array_merge($this->viewManager->getShared(), $this->data);
    }
    public function __get(string $key)
    {
        return $this->data[$key];
    }
    public function __set(string $key, mixed $value): void
    {
        $this->with($key, $value);
    }
    public function __isset(string $key)
    {
        return isset($this->data[$key]);
    }
    public function __unset(string $key)
    {
        unset($this->data[$key]);
    }
    public function __toString()
    {
        return $this->render();
    }
}
