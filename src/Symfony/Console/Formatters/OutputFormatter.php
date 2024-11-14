<?php

namespace Src\Symfony\Console\Formatters;

class OutputFormatter implements IOutputFormatter
{
    public function __construct(
        protected bool $decorated = false
    ) {}
    public static function escape(string $text): string
    {
        $text = preg_replace('/([^\\\\]|^)([<>])/', '$1\\\\$2', $text);

        return self::escapeTrailingBackslash($text);
    }
    public static function escapeTrailingBackslash(string $text): string
    {
        if (str_ends_with($text, '\\')) {
            $len = \strlen($text);
            $text = rtrim($text, '\\');
            $text = str_replace("\0", '', $text);
            $text .= str_repeat("\0", $len - \strlen($text));
        }

        return $text;
    }
    public function setDecorated(bool $decorated): void
    {
        $this->decorated = $decorated;
    }
    public function isDecorated(): bool
    {
        return $this->decorated;
    }
    public function format(?string $message): ?string
    {
        return $message;
    }
}
