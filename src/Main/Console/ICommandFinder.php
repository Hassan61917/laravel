<?php

namespace Src\Main\Console;

interface ICommandFinder
{
    public function find(array $paths, array $except = []): array;
}
