<?php

namespace Src\Main\Log\Loggers;

use Src\Main\Filesystem\Filesystem;
use Src\Main\Log\ILogger;

class CustomLogger implements ILogger
{
    protected Filesystem $files;
    protected string $filePath;
    protected array $levels = [
        "Info",
        "Debug",
        "Error",
        "Emergency"
    ];
    public function __construct(
        protected array $config = []
    ) {
        $this->files = new Filesystem();
        $this->filePath = $this->config['path'];
    }
    public function emergency(string $message, array $context = []): void
    {
        $this->log(3, $message, $context);
    }
    public function error(string $message, array $context = []): void
    {
        $this->log(2, $message, $context);
    }
    public function debug(string $message, array $context = []): void
    {
        $this->log(1, $message, $context);
    }
    public function info(string $message, array $context = []): void
    {
        $this->log(0, $message, $context);
    }
    public function log(int $level, string $message, array $context = []): void
    {
        $this->files->append($this->filePath, $this->formatMessage($level, $message, $context));
    }
    private function formatMessage(int $level, string $message, array $context): string
    {
        $type = $this->levels[$level];

        $contextMessage = "";

        foreach ($context as $key => $value) {
            $key = is_string($key) ? $key . ":" : "";
            $contextMessage .= "{$key}{$value}\n";
        }

        return "{$type}:{$message} \n{$contextMessage}\n";
    }
}
