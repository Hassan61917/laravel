<?php

namespace Src\Main\Auth;

class Recaller
{
    protected string $recaller;
    public function __construct(string $recaller)
    {
        $this->recaller = @unserialize($recaller, ['allowed_classes' => false]) ?: $recaller;
    }
    public function id(): string
    {
        return $this->createSegments()[0];
    }
    public function token(): string
    {
        return $this->createSegments()[1];
    }
    public function password(): string
    {
        return $this->createSegments()[2];
    }
    public function valid(): bool
    {
        return $this->properString() && $this->hasAllSegments();
    }
    protected function properString(): bool
    {
        return str_contains($this->recaller, '|');
    }
    protected function hasAllSegments(): bool
    {
        $segments = $this->createSegments();

        return count($segments) >= 3 &&
            !empty(trim($segments[0])) &&
            !empty(trim($segments[1])) &&
            !empty(trim($segments[2]));
    }
    protected function createSegments(): array
    {
        return explode('|', $this->recaller, 3);
    }
}
