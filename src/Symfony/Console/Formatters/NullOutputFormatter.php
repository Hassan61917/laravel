<?php

namespace Src\Symfony\Console\Formatters;

class NullOutputFormatter implements IOutputFormatter
{

    public function setDecorated(bool $decorated): void
    {
        // TODO: Implement setDecorated() method.
    }
    public function isDecorated(): bool
    {
        return false;
    }
    public function format(?string $message): ?string
    {
        return $message;
    }
}
