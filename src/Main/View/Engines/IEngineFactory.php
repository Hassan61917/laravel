<?php

namespace Src\Main\View\Engines;

interface IEngineFactory
{
    public function make(string $name): IEngine;
}
