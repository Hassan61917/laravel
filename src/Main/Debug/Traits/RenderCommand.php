<?php

namespace Src\Main\Debug\Traits;

use Src\Main\Console\AppCommand;
use Throwable;

trait RenderCommand
{
    protected function renderCommand(AppCommand $command, Throwable $e): void
    {
        $output = $command->getOutput();
        $file = $e->getFile();
        $line = $e->getLine();
        $lines = $this->getErrorLines($file, $line);
        $content = $this->formatContent($lines, $line);
        $output->write("Error Message:\t" . $e->getMessage() . "\n\n");
        $output->write($content . "\n");
        $output->write("$file:$line\n");
    }
    protected function getErrorLines(string $file, int $line): array
    {
        $body = explode("\n", file_get_contents($file));
        return array_splice($body, $line - 4, 6);
    }
    protected function formatContent(array $content, int $line): string
    {
        $result = [];
        $start = $line - 3;
        foreach ($content as $item) {
            $item = trim($item);
            if ($start === $line) {
                $result[] = "$start|====> $item\n";
            } else {
                $result[] = "$start|\t$item\n";
            }
            $start++;
        }
        return implode("", $result);
    }
}
