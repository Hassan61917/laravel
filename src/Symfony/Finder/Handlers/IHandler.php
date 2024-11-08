<?php

namespace Src\Symfony\Finder\Handlers;

interface IHandler
{
    public function handle(): \Iterator;
}
