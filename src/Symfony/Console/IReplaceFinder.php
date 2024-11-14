<?php

namespace Src\Symfony\Console;

interface IReplaceFinder
{
    public function find(string $name, array $allCommands): array;
}
