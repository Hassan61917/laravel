<?php

namespace Src\Main\Console;

use SplFileInfo;
use Src\Main\Utils\Str;
use Src\Symfony\Finder\Finder;

class CommandFinder implements ICommandFinder
{
    protected array $except = [
        "Traits",
        "Abstract"
    ];
    public function find(array $paths, array $except = []): array
    {
        $except = array_merge($except, $this->except);

        return $this->findCommands($paths, $except);
    }
    protected function findCommands(array $paths, array $except): array
    {
        $result = [];

        foreach ($paths as $path) {
            array_push($result, ...$this->getCommands($path, $except));
        }

        return $result;
    }
    protected function getCommands(string $path, array $except): array
    {
        $result = [];

        foreach (Finder::create()->in($path)->name(".php") as $file) {
            $command = $this->toCommand($file);
            if ($this->isValidCommand($command, $except)) {
                $result[] = $command;
            }
        }

        return $result;
    }
    protected function toCommand(SplFileInfo $file): string
    {
        $basePath = rtrim(base_path()) . "\\";
        $filePath = $file->getRealPath();
        [$namespace, $path] = explode("\\", Str::after($filePath, $basePath), 2);
        $path = Str::ucfirst($namespace) . "\\" . $path;
        return str_replace([".php", '/'], ["", "\\"], $path);
    }
    protected function isValidCommand(string $command, array $except): bool
    {
        if (!is_subclass_of($command, AppCommand::class)) {
            return false;
        }

        foreach ($except as $word) {
            if (str_contains($command, $word)) {
                return false;
            }
        }

        return true;
    }
}
