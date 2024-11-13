<?php

namespace Src\Main\View\Finders;

interface IFinder
{
    public function find(string $name, array $extensions = []): string;
}
