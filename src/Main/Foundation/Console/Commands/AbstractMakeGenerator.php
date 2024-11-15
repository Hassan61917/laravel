<?php

namespace Src\Main\Foundation\Console\Commands;

use Src\Main\Console\AppCommand;
use Src\Main\Filesystem\Filesystem;
use Src\Main\Foundation\Console\Traits\FileHelper;
use Src\Main\Utils\Str;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;
use Src\Symfony\Finder\Finder;

abstract class AbstractMakeGenerator extends AppCommand
{
    use FileHelper;
    protected string $path = "";
    protected string $type;
    protected string $stubsPath = "";
    protected Filesystem $files;
    public function __construct()
    {
        parent::__construct();
        $this->files = new Filesystem();
    }
    public function handle(): bool
    {
        $name = $this->buildName();

        $filePath = $this->getFilePath($name);

        if (!$this->optionExists("force") && $this->exists($filePath)) {
            $this->write("{$this->type} {$name} already exists.");
            return false;
        }

        $this->makeDirectory($filePath);

        $this->put($filePath, $name);

        $this->write("{$this->type} {$name} created successfully.\n");

        return false;
    }
    protected function buildName(): string
    {
        $name = $this->getNameInput();

        if (!str_ends_with($name, $this->type)) {
            $name .= $this->type;
        }

        return $this->qualifyClass($name);
    }
    protected function getArguments(): array
    {
        return [
            new InputArgument("name", "The name of the {$this->type}", InputMode::Required),
        ];
    }
    protected function getOptions(): array
    {
        return array_merge($this->getCommandOptions(), $this->getGlobalOptions());
    }
    protected function getCommandOptions(): array
    {
        return [];
    }
    protected function getGlobalOptions(): array
    {
        return  [
            new InputOption('force', null, 'Create the class even if the class already exists', InputMode::None)
        ];
    }
    protected function getNameInput(): string
    {
        return trim($this->getInputArgument("name"));
    }
    protected function qualifyClass(string $name): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $namespace = $this->rootNamespace();

        if (str_starts_with($name, $namespace . "\\")) {
            return $name;
        }

        $namespace = $this->getBasePath($namespace);

        $name = "{$namespace}/{$name}";

        return str_replace('/', "\\", $name);
    }
    protected function rootNamespace(): string
    {
        return $this->getLaravel()->getNamespace();
    }
    protected function getBasePath(string $namespace): string
    {
        return "$namespace/{$this->path}";
    }
    protected function getFilePath(string $name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        $name = str_replace('\\', '/', trim($name, '\\'));

        $path = trim(base_path(strtolower($this->rootNamespace())), "/");

        return "$path/$name.php";
    }
    protected function buildClass(string $name): string
    {
        $stubPath = $this->getStub();

        $stub = $this->read($stubPath);

        $stub =  $this->replaceNamespace($stub, $name);

        return $this->replaceClass($stub, $name);
    }
    protected function getStub(): string
    {
        return $this->getStubsPath() . "\\" . $this->getStubByOption($this->getDefaultStub());
    }
    protected function getStubsPath(): string
    {
        $path = dirname(__DIR__);
        return "{$path}\\Stubs\\{$this->stubsPath}";
    }
    protected function getStubByOption(string $default): string
    {
        $result = $default;
        foreach ($this->getOptions() as $option) {
            $name = $option->getName();
            $optionStub = $this->findStub($name);
            if ($this->optionExists($name) && $optionStub) {
                $result = $optionStub;
            }
        }
        return $result;
    }
    protected function findStub(string $option): ?string
    {
        $path =  $this->getStubsPath();
        foreach (Finder::create()->in($path)->name(".stub") as $stub) {
            $name = $stub->getFilename();
            if (str_contains($name, strtolower("{$this->type}.{$option}"))) {
                return $name;
            }
        }
        return null;
    }
    protected function replaceNamespace(string $stub, string $name): string
    {
        $search = ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}'];
        return str_replace(
            $search,
            [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel()],
            $stub
        );
    }
    protected function getNamespace(string $name): string
    {
        $parts = explode('\\', $name);
        return implode('\\', array_slice($parts, 0, -1));
    }
    protected function userProviderModel(): string
    {
        return "App\Models\User";
    }
    protected function replaceClass(string $stub, string $name): string
    {
        $class = str_replace($this->getNamespace($name) . '\\', '', $name);

        return str_replace('{{class}}', $class, $stub);
    }
    protected function qualifyModel(string $model): string
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->rootNamespace();

        if (str_starts_with($model, $rootNamespace)) {
            return $model;
        }

        return $rootNamespace . "\\Models\\" . $model;
    }
    protected function possibleModels(): array
    {
        $modelPath = $this->laravel->path("Models");

        $files = Finder::create()->in($modelPath)->depth(1)->name(".php");

        return array(...$files);
    }
    protected abstract function getDefaultStub(): string;
}
