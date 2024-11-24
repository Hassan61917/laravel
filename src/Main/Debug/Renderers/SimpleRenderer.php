<?php

namespace Src\Main\Debug\Renderers;

use Src\Main\Debug\IExceptionRenderer;
use Src\Main\View\ViewManager;

class SimpleRenderer implements IExceptionRenderer
{
    public function __construct(
        protected ViewManager $viewManager
    ) {}
    public function render(\Throwable $exception): string
    {
        $stack = $this->formatStack($exception->getTraceAsString());

        $message = $exception->getMessage();

        $path = dirname(__DIR__) . "/views/view.custom.php";

        return $this->viewManager
            ->file($path, compact('stack', 'message'))
            ->render();
    }
    protected function formatStack(string $msg): array
    {
        $messages = $this->extractMessages($msg);

        return $this->extractAddresses($messages);
    }
    protected function extractMessages(string $msg): array
    {
        $messages = explode("#", $msg);

        $messages = array_splice($messages, 1);

        $messages = array_map(fn($m) => substr($m, strpos($m, " ") + 1), $messages);

        array_pop($messages);

        return $messages;
    }
    protected function extractAddresses(array $messages): array
    {
        $result = [
            $this->extractFileAdders(array_pop($messages))
        ];

        foreach ($messages as $message) {
            $file = $this->extractFileAdders($message);

            if ($file != "[internal function]") {
                $result[] = $file;
            }
        }

        return $result;
    }
    protected function extractFileAdders(mixed $message): string
    {
        $pos = strpos($message, ":", 2);

        return substr($message, 0, $pos);
    }
}
