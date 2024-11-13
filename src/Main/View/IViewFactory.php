<?php

namespace Src\Main\View;

interface IViewFactory
{
    public function exists(string $name): bool;
    public function file(string $path, array $data = []): View;
    public function make(string $name, array $data = []): View;
    public function share(string $key, mixed $value = null): static;
}
