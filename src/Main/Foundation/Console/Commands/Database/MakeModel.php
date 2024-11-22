<?php

namespace Src\Main\Foundation\Console\Commands\Database;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;
use Src\Main\Utils\Str;
use Src\Symfony\Console\Inputs\Item\InputOption;

class MakeModel extends AbstractMakeGenerator
{
    protected string $path = "Models";
    protected string $type = "Model";
    protected string $stubsPath = "Database";
    protected string $description = 'Create a new model class';
    protected bool $addsType = false;
    public function handle(): bool
    {
        parent::handle();

        if ($this->getOption('all')) {
            $this->input->setOption('factory', true);
            $this->input->setOption('seed', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('controller', true);
            $this->input->setOption('policy', true);
            $this->input->setOption('resource', true);
        }

        if ($this->getOption('factory')) {
            $this->createFactory();
        }

        if ($this->getOption('migration')) {
            $this->createMigration();
        }

        if ($this->getOption('seed')) {
            $this->createSeeder();
        }

        if ($this->getOption('controller') || $this->getOption('resource') || $this->getOption('api')) {
            $this->createController();
        }

        if ($this->getOption('policy')) {
            $this->createPolicy();
        }

        return true;
    }
    protected function createFactory(): void
    {
        $factory = Str::studly($this->getInputName());

        $this->call('make:factory', [
            'name' => "{$factory}Factory",
        ]);
    }
    protected function createMigration(): void
    {
        $table = Str::snake(Str::pluralStudly(class_basename($this->getInputName())));

        $this->call('make:migration', [
            'name' => "create_{$table}_table",
            '--create' => $table,
        ]);
    }
    protected function createSeeder(): void
    {
        $seeder = Str::studly(class_basename($this->getInputName()));

        $this->call('make:seeder', [
            'name' => "{$seeder}Seeder",
        ]);
    }
    protected function createController(): void
    {
        $controller = Str::studly(class_basename($this->getInputName()));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:controller', array_filter([
            'name' => "{$controller}Controller",
            '--model' => $this->getOption('resource') || $this->getOption('api') ? $modelName : null,
            '--api' => $this->getOption('api'),
        ]));
    }
    protected function createPolicy(): void
    {
        $policy = Str::studly(class_basename($this->getInputName()));

        $this->call('make:policy', [
            'name' => "{$policy}Policy",
            '--model' => $this->qualifyClass($this->getNameInput()),
        ]);
    }
    protected function getInputName(): ?string
    {
        return $this->getInputArgument('name');
    }
    protected function getDefaultStub(): string
    {
        return "model.stub";
    }
    protected function getCommandOptions(): array
    {
        return [
            new InputOption("all", "a", 'Generate a migration, seeder, factory, policy, resource controller, and form request classes for the model'),
            new InputOption("controller", "c", 'Create a new controller for the model'),
            new InputOption("factory", "f", 'Create a new factory for the model'),
            new InputOption("migration", "m", 'Create a new migration file for the model'),
            new InputOption("policy", "p", 'Create a new policy for the model'),
            new InputOption("seed", "s", 'Create a new seeder for the model'),
            new InputOption("resource", "r", 'Indicates if the generated controller should be a resource controller'),
            new InputOption("api", null, "Indicates if the generated controller should be an API resource controller"),
            new InputOption("requests", "R", 'Create new form request classes and use them in the resource controller'),
        ];
    }
}
