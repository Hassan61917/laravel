<?php

namespace Src\Symfony\Console\Exceptions;

use Exception;

class CommandNotFoundException extends Exception
{
    public function __construct(
        string $message,
        protected array $commands = []
    ) {
        parent::__construct($message);
    }
    public function getCommands(): array
    {
        return $this->commands;
    }
}
