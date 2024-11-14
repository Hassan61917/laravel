<?php

namespace Src\Symfony\Console\Descriptors;

use Src\Symfony\Console\Outputs\IConsoleOutput;

interface Describable
{
    public function describe(IDescriptor $descriptor, IConsoleOutput $output, array $options = []): void;
}
