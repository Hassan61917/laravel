<?php

namespace Src\Symfony\Console\Outputs;

use Src\Symfony\Console\Formatters\IOutputFormatter;
use Src\Symfony\Console\Formatters\OutputFormatter;

abstract class Output implements IConsoleOutput
{
    protected ?IOutputFormatter $formatter = null;
    public function __construct(
        protected bool $decorated = false,
    ) {
        $this->formatter = new OutputFormatter();
        $this->formatter->setDecorated($this->decorated);
    }
    public function setFormatter(?IOutputFormatter $formatter): void
    {
        $this->formatter = $formatter;
    }
    public function getFormatter(): IOutputFormatter
    {
        return $this->formatter;
    }
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
    }
    public function isDecorated(): bool
    {
        return $this->decorated;
    }
    public function writeln(string $messages): void
    {
        $this->write($messages, true);
    }
    public function write(string $messages, bool $newline = false): void
    {
        $messages = [$messages];

        foreach ($messages as $message) {
            $message =  $this->formatter->format($message);
            $this->doWrite($message ?? '', $newline);
        }
    }
    abstract protected function doWrite(string $message, bool $newline): void;
}
