<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\IConnectionResolver;
use Src\Main\Database\Seeder;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class DbSeed extends AbstractDbCommand
{
    protected string $description = 'Seed the database with records';
    protected string $defaultSeeder = 'Database\\Seeders\\DatabaseSeeder';
    public function __construct(
        protected IConnectionResolver $resolver
    ) {
        parent::__construct();
    }
    public function handle(): int
    {
        $this->output->write('Seeding database.');

        $previousConnection = $this->resolver->getDefaultConnection();

        $this->resolver->setDefaultConnection($this->getDatabase());

        Model::unguarded(function () {
            $this->getSeeder()->__invoke();
        });

        if ($previousConnection) {
            $this->resolver->setDefaultConnection($previousConnection);
        }

        return 0;
    }
    protected function getSeeder(): Seeder
    {
        $class = $this->input->getArgument('class') ?? $this->input->getOption('class');

        if (! str_contains($class, '\\')) {
            $class = 'Database\\Seeders\\' . $class;
        }

        if ($class === $this->defaultSeeder && ! class_exists($class)) {
            $class = 'DatabaseSeeder';
        }

        return $this->laravel->make($class)
            ->setContainer($this->laravel)
            ->setCommand($this);
    }
    protected function getArguments(): array
    {
        return [
            new InputArgument("class", 'The class name of the root seeder', InputMode::Optional)
        ];
    }
    protected function extraOptions(): array
    {
        return [
            new InputOption(
                "class",
                null,
                'The class name of the root seeder',
                InputMode::Optional,
                $this->defaultSeeder
            ),
        ];
    }
}
