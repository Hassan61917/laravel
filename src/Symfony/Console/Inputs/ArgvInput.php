<?php

namespace Src\Symfony\Console\Inputs;

use InvalidArgumentException;
use RuntimeException;

class ArgvInput extends ConsoleInput
{
    protected array $parsed = [];
    public function __construct()
    {
        parent::__construct(array_splice($_SERVER['argv'], 1));
    }
    protected function parse(): void
    {
        $this->parsed = $this->tokens;

        while ($token = array_shift($this->parsed)) {
            $this->parseToken($token);
        }
    }
    protected function parseToken(string $token): void
    {
        if (!$this->isOption($token)) {
            $this->parseArgument($token);
        } else {
            $this->parseOption($token);
        }
    }
    protected function parseArgument(string $token): void
    {
        $argument = $this->item->popArgument();

        if ($argument) {
            $this->addArgument($argument->getName(), $token);
        } else {
            $commandName = $this->arguments["command"];
            throw new RuntimeException("Too many arguments for {$commandName} command");
        }
    }
    protected function parseOption(string $token): void
    {
        if ($this->isLongOption($token)) {
            $this->parseLongOption($token);
        } else {
            $this->parseShortOption($token);
        }
    }
    protected function parseLongOption(string $token): void
    {
        $name = substr($token, 2);

        if (empty($name)) {
            return;
        }

        $pos = strpos($name, "=");

        if ($pos) {
            $value = substr($name, $pos + 1);

            $name = substr($name, 0, $pos);
        } else {
            $value = null;
        }

        $this->addLongOption($name, $value);
    }
    protected function parseShortOption(string $token): void
    {
        $name = substr($token, 1);

        if (strlen($name) > 1) {
            $this->parseShortOptionSet($name);
        } else {
            $this->addShortOption($name);
        }
    }
    protected function getValueFromTokens(): ?string
    {
        if (count($this->parsed)) {
            $next = array_shift($this->parsed);
            if (!$this->isOption($next)) {
                return $next;
            } else {
                array_unshift($this->parsed, $next);
            }
        }
        return null;
    }
}
