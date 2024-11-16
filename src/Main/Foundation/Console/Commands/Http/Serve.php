<?php

namespace Src\Main\Foundation\Console\Commands\Http;

use Src\Main\Console\AppCommand;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class Serve extends AppCommand
{
    protected string $description = 'Serve the application on the PHP development server';
    public function handle(): void
    {
        $host = $this->getOption("host");
        $port = $this->getOption("port");
        exec("php -S {$host}:{$port} -t public");
    }
    protected function getOptions(): array
    {
        return [
            new InputOption(
                "host",
                null,
                "The host address to serve the application on",
                InputMode::Optional,
                env('SERVER_HOST', '127.0.0.1')
            ),
            new InputOption(
                "port",
                null,
                "The port to serve the application on",
                InputMode::Optional,
                env('SERVER_PORT', 5000)
            ),
        ];
    }
}
