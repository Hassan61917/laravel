<?php

namespace Src\Symfony\Console;

class ReplaceFinder implements IReplaceFinder
{
    public function find(string $name, array $allCommands): array
    {
        $expr = implode('[^:]*:', array_map('preg_quote', explode(':', $name))) . '[^:]*';

        $commands = preg_grep('{^' . $expr . '}i', $allCommands);

        return count($commands) > 0 ? $commands : $allCommands;
    }
}
