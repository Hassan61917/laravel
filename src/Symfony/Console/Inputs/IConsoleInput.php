<?php

namespace Src\Symfony\Console\Inputs;

use Src\Symfony\Console\Inputs\Item\InputItem;

interface IConsoleInput
{
    public function getFirstArgument(): ?string;
    public function getArguments(): array;
    public function getArgument(string $name): ?string;
    public function setArgument(string $name, mixed $value): void;
    public function hasArgument(string $name): bool;
    public function hasParameterOption(string ...$values): bool;
    public function getOptions(): array;
    public function getOption(string $name): ?string;
    public function setOption(string $name, mixed $value): void;
    public function hasOption(string $name): bool;
    public function optionExists(string $name): bool;
    public function bind(InputItem $item): void;
    public function validate(): void;
}
