<?php

namespace Src\Symfony\Console\Outputs;

use Src\Symfony\Console\Formatters\IOutputFormatter;

interface IConsoleOutput
{
    public function write(string $messages, bool $newline = false): void;
    public function writeln(string $messages): void;
    public function setDecorated(bool $decorated): void;
    public function isDecorated(): bool;
    public function setFormatter(IOutputFormatter $formatter): void;
    public function getFormatter(): IOutputFormatter;
}
