<?php

namespace Src\Main\Foundation\Console;

use Src\Symfony\Console\Inputs\IConsoleInput;
use Src\Symfony\Console\Outputs\IConsoleOutput;

interface IConsoleKernel
{
    public function handle(IConsoleInput $input, IConsoleOutput $output): int;
    public function terminate(IConsoleInput $input, int $status): void;
}
