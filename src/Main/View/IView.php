<?php

namespace Src\Main\View;

interface IView
{
    public function render(): string;
    public function name(): string;
    public function with(string $key, mixed $value = null): static;
    public function getData(): array;
}
