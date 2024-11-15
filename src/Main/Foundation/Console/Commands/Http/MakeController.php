<?php

namespace Src\Main\Foundation\Console\Commands\Http;

use InvalidArgumentException;
use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class MakeController extends AbstractMakeGenerator
{
    protected string $path = "Http/Controllers";
    protected string $type = "Controller";
    protected string $stubsPath = "Http";
    protected string $description = 'Create a new Controller class';
    protected function getCommandOptions(): array
    {
        return [
            new InputOption("model", "m", "create resource controller for the model", InputMode::Required),
            new InputOption("api", null, "Create Api Controller", InputMode::None),
        ];
    }
    protected function getDefaultStub(): string
    {
        return "controller.plain.stub";
    }
    protected function buildClass(string $name): string
    {
        $replace = [];

        if (
            $this->optionExists('model') ||
            $this->optionExists('api')
        ) {
            $replace = $this->buildModelReplacements($replace);
        }
        return str_replace(
            array_keys($replace),
            array_values($replace),
            parent::buildClass($name)
        );
    }
    protected function buildModelReplacements(array $replace): array
    {
        $model = $this->getOption('model');

        if (!$model) {
            throw new InvalidArgumentException("Model option is required");
        }

        $modelClass = $this->parseModel($model);

        if (!class_exists($modelClass)) {
            throw new InvalidArgumentException("Class {$modelClass} does not exists.");
        }

        return array_merge($replace, [
            '{{namespacedModel}}' => $modelClass,
            '{{model}}' => class_basename($modelClass),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }
    protected function parseModel(string $model): string
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        return $this->qualifyModel($model);
    }
}
