<?php

namespace Src\Main\View\Engines;

interface IEngine
{
    public function get(string $path, array $data = []): string;
}
