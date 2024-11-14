<?php

namespace Src\Symfony\Console\Outputs;

use Src\Symfony\Console\Formatters\IOutputFormatter;
use Src\Symfony\Console\Formatters\NullOutputFormatter;

class NullOutput implements IConsoleOutput
{
    public function write(string $messages, bool $newline = false): void
    {
        // TODO: Implement write() method.
    }
    public function writeln(string $messages): void
    {
        // TODO: Implement writeln() method.
    }
    public function setDecorated(bool $decorated): void
    {
        // TODO: Implement setDecorated() method.
    }
    public function isDecorated(): bool
    {
        return false;
    }
    public function setFormatter(IOutputFormatter $formatter): void
    {
        // TODO: Implement setFormatter() method.
    }
    public function getFormatter(): IOutputFormatter
    {
        return new NullOutputFormatter();
    }
}
