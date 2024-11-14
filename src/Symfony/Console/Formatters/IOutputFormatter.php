<?php

namespace Src\Symfony\Console\Formatters;

interface IOutputFormatter
{
    public function setDecorated(bool $decorated): void;
    public function isDecorated(): bool;
    public function format(?string $message): ?string;
}
