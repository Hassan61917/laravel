<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Database\Connections\Connection;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class DbWipe extends AbstractDbCommand
{
    protected string $description = 'Drop all tables, views, and types';
    public function handle(): int
    {
        $database = $this->getDatabase();

        if ($this->getOption('drop-views')) {
            $this->dropAllViews($database);

            $this->output->write('Dropped all views successfully.');
        }

        $this->dropAllTables($database);

        $this->output->write('Dropped all tables successfully.');

        return 0;
    }
    protected function dropAllViews(string $database): void
    {
        $this->getConnection($database)
            ->getSchemaBuilder()
            ->dropAllViews();
    }
    protected function dropAllTables(string $database): void
    {
        $this->getConnection($database)
            ->getSchemaBuilder()
            ->dropAllTables();
    }
    protected function getConnection(string $database): Connection
    {
        return $this->laravel['db']->connection($database);
    }
    protected function extraOptions(): array
    {
        return [
            new InputOption("drop-views", null, 'Drop all tables and views', InputMode::None)
        ];
    }
}
