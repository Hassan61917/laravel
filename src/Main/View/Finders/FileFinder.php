<?php

namespace Src\Main\View\Finders;

use InvalidArgumentException;
use Src\Main\Container\IContainer;

class FileFinder implements IFinder
{
    public function __construct(
        protected IContainer $container
    ) {}
    public function getPaths(): array
    {
        return $this->container["config"]["view.paths"];
    }
    public function find(string $name, array $extensions = []): string
    {
        $files = $this->getPossibleViewFiles($name, $extensions);

        foreach ($this->getPaths() as $path) {
            foreach ($files as $file) {
                $file = "{$path}\\{$file}";
                if (file_exists($file)) {
                    return $file;
                }
            }
        }

        throw new InvalidArgumentException("View $name not found.");
    }
    protected function getPossibleViewFiles(string $name, array $extensions): array
    {
        return array_map(fn($extension) => $this->formatFile($name, $extension), $extensions);
    }
    protected function formatFile(string $name, string $extension): string
    {
        return str_replace('.', '/', $name) . $extension;
    }
}
