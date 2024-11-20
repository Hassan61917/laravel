<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Console\AppCommand;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class AbstractDbCommand extends AppCommand
{
    protected function getDatabase(): string
    {
        return $this->input->getOption('database') ??
            $this->laravel["config"]["database.default"];
    }
    protected function getOptions(): array
    {
        $options =  [
            new InputOption("database", null, 'The database connection to use', InputMode::Optional),
            new InputOption("force", null, 'Force the operation to run when in production', InputMode::Optional),
        ];

        return array_merge(
            parent::getOptions(),
            $this->extraOptions(),
            $options
        );
    }
    protected function extraOptions(): array
    {
        return [];
    }
}
