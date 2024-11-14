<?php

namespace Src\Symfony\Console\Helpers;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

class HelperSet implements IteratorAggregate
{
    protected array $helpers = [];
    public function __construct(array $helpers = [])
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, is_int($alias) ? null : $alias);
        }
    }
    public function set(IHelper $helper, ?string $alias = null): void
    {
        $this->helpers[$helper->getName()] = $helper;

        if ($alias) {
            $this->helpers[$alias] = $helper;
        }

        $helper->setHelperSet($this);
    }
    public function has(string $name): bool
    {
        return isset($this->helpers[$name]);
    }
    public function get(string $name): IHelper
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException("The helper $name is not defined.");
        }

        return $this->helpers[$name];
    }
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->helpers);
    }
}
