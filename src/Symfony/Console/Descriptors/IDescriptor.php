<?php

namespace Src\Symfony\Console\Descriptors;

use Src\Symfony\Console\Application;
use Src\Symfony\Console\Commands\Command;
use Src\Symfony\Console\Outputs\IConsoleOutput;

interface IDescriptor
{
    public function describeCommand(Command $command, IConsoleOutput $output, array $options = []): void;
    public function describeApplication(Application $application, IConsoleOutput $output, array $options = []): void;
}
