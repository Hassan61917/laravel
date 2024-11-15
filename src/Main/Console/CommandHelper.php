<?php

namespace Src\Main\Console;

class CommandHelper
{
    public static function createName(string $name): string
    {
        $name = preg_replace('/(.)(?=[A-Z])/u', "$1" . ":", $name);

        return strtolower($name);
    }
}
