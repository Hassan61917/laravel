<?php

namespace Src\Main\Foundation\Console\Commands\Events;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class MakeObserver extends AbstractMakeGenerator
{
    protected string $path = "Observers";
    protected string $type = "Observer";
    protected string $stubsPath = "Events";
    protected string $description = 'Create a new Observer class';
    protected function getDefaultStub(): string
    {
        return "observer.stub";
    }
    protected function getCommandOptions(): array
    {
        return [
            new InputOption("model", "m", 'The model that the observer applies to', InputMode::Optional),
        ];
    }
    protected function buildClass(string $name): string
    {
        $stub = parent::buildClass($name);

        $model = $this->getOption('model');

        return $model ? $this->replaceModel($stub, $model) : $stub;
    }
    protected function replaceModel(string $stub, string $model): string
    {
        $modelClass = $this->qualifyModel($model);

        $replace = [
            '{{modelNamespace}}' => $modelClass,
            '{{model}}' => class_basename($modelClass),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $stub
        );
    }
}
