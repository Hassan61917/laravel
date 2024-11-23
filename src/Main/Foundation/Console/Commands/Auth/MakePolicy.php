<?php

namespace Src\Main\Foundation\Console\Commands\Auth;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;
use Src\Main\Utils\Str;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;
class MakePolicy extends AbstractMakeGenerator
{
    protected string $path = "Policies";
    protected string $type = "Policy";
    protected string $stubsPath = "Auth";
    protected string $description = 'Create a new policy class';
    protected function getDefaultStub(): string
    {
        return "policy.stub";
    }
    protected function getCommandOptions(): array
    {
        return [
            new InputOption("model", "m", "'The model that the policy applies to'", InputMode::Optional),
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
        $model = str_replace('/', '\\', $model);

        $model = class_basename(trim($model, '\\'));

        $user = class_basename($this->userProviderModel());

        $modelVariable = Str::camel($model) === 'user' ? 'model' : $model;

        $replace = [
            '{{model}}' => $model,
            '{{modelVariable}}' => Str::camel($modelVariable),
            '{{user}}' => $user,
            '$user' => '$' . Str::camel($user),
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            $stub
        );
    }
}
