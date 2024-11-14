<?php

namespace Src\Symfony\Console\Loaders;

use Src\Symfony\Console\Commands\Command;

interface ICommandLoader
{
    public function get(string $name): Command;
    public function has(string $name): bool;
    public function getNames(): array;
}
