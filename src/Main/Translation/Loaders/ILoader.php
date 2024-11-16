<?php

namespace Src\Main\Translation\Loaders;

interface ILoader
{
    public function load(string $language, string $group): array;
}
