<?php

namespace Src\Main\View\Finders;

class ViewFinder implements IFinder
{
    protected array $views = [];
    public function __construct(
        protected IFinder $finder
    ) {}
    public function find(string $name, array $extensions = []): string
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        return $this->views[$name] = $this->finder->find($name, $extensions);
    }
}
