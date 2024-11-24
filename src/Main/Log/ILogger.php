<?php

namespace Src\Main\Log;

interface ILogger
{
    public function emergency(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function info(string $message, array $context = []): void;
    public function debug(string $message, array $context = []): void;
    public function log(int $level, string $message, array $context = []): void;
}
