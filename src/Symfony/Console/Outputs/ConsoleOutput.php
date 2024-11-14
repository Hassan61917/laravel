<?php

namespace Src\Symfony\Console\Outputs;

class ConsoleOutput extends StreamOutput
{
    public function __construct(
        bool $decorated = false
    ) {
        parent::__construct($this->openOutputStream(), $decorated);
    }
    protected function openOutputStream()
    {
        return fopen('php://output', 'w');
    }
}
