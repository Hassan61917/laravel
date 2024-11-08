<?php

namespace Src\Symfony\Finder;

use InvalidArgumentException;
use IteratorAggregate;
use Src\Symfony\Finder\Handlers\DateHandler;
use Src\Symfony\Finder\Handlers\ExcludeHandler;
use Src\Symfony\Finder\Handlers\FileHandler;
use Src\Symfony\Finder\Handlers\RecursiveHandler;
use Traversable;

class Finder implements IteratorAggregate
{
    protected array $depths = [];
    protected array $names = [];
    protected array $contains = [];
    protected array $paths = [];
    protected array $exclude = [];
    protected array $dirs = [];
    protected array $dates = [];
    public static function create(): static
    {
        return new static();
    }
    public function depth(int $depth): static
    {
        return $this->addDepth("<= {$depth}");
    }
    public function addDepth(string $depth): static
    {
        $this->depths[] = $depth;

        return $this;
    }
    public function name(string ...$patterns): static
    {
        array_push($this->names, ...$patterns);

        return $this;
    }
    public function contains(string ...$patterns): static
    {
        array_push($this->contains, ...$patterns);

        return $this;
    }
    public function path(string ...$patterns): static
    {
        array_push($this->paths, ...$patterns);

        return $this;
    }
    public function exclude(string ...$patterns): static
    {
        array_push($this->exclude, ...$patterns);

        return $this;
    }
    public function in(string ...$dirs): static
    {
        $resolvedDirs = [];

        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $resolvedDirs[] = $this->normalizeDir($dir);
            } else {
                throw new InvalidArgumentException("The {$dir} directory does not exist.");
            }
        }

        array_push($this->dirs, ...$resolvedDirs);

        return $this;
    }
    public function date(string $operation, int $time): static
    {
        $this->dates[] = [$operation, $time];

        return $this;
    }
    public function getIterator(): Traversable
    {
        $iterators = [];
        foreach ($this->dirs as $dir) {
            $iterators[] = $this->searchInDirectory($dir);
        }
        foreach ($iterators as $iterator) {
            yield from $iterator;
        }
    }
    private function normalizeDir(string $dir): string
    {
        if ($dir === "/") {
            return $dir;
        }

        return rtrim($dir, "/");
    }
    private function searchInDirectory(string $dir): \Iterator
    {
        $exclude = $this->exclude;

        [$minDepth, $maxDepth] = $this->handleDepth();

        $handler = new RecursiveHandler($dir, $minDepth, $maxDepth);

        if ($exclude) {
            $handler = new ExcludeHandler($handler, $exclude);
        }

        if ($this->names) {
            $handler = new FileHandler($handler, $this->names);
        }

        if ($this->dates) {
            $handler = new DateHandler($handler, $this->dates);
        }

        return $handler->handle();
    }
    protected function handleDepth(): array
    {
        $minDepth = 0;
        $maxDepth = \PHP_INT_MAX;

        foreach ($this->depths as $depth) {
            [$operator, $value] = explode(" ", $depth);
            $value = +$value;
            switch ($operator) {
                case '>':
                    $minDepth = $value + 1;
                    break;
                case '>=':
                    $minDepth = $value;
                    break;
                case '<':
                    $maxDepth = $value - 1;
                    break;
                case '<=':
                    $maxDepth = $value;
                    break;
            }
        }
        return array($minDepth, $maxDepth);
    }
}
