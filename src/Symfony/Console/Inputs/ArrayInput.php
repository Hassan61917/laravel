<?php

namespace Src\Symfony\Console\Inputs;

class ArrayInput extends ConsoleInput
{
    protected function parse(): void
    {
        foreach ($this->tokens as $key => $value) {
            if ($this->isLongOption($key)) {
                $this->addLongOption(substr($key, 2), $value);
            } elseif ($this->isShortOption($key)) {
                $this->addShortOption(substr($key, 1), $value);
            } else {
                $this->addArgument($key, $value);
            }
        }
    }
}
