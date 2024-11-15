<?php

namespace Src\Main\Console;

use InvalidArgumentException;
use Src\Symfony\Console\Inputs\Item\InputArgument;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class SignatureParser
{
    public static function getName(string $expression): string
    {
        if (! preg_match('/\S+/', $expression, $matches)) {
            throw new InvalidArgumentException('Unable to determine command name from signature.');
        }

        return $matches[0];
    }
    public static function getParameters(string $expression): array
    {
        $arguments = [];

        $options = [];

        if (preg_match_all('/\{\s*(.*?)\s*}/', $expression, $matches) && count($matches[1])) {
            foreach ($matches[1] as $token) {
                if (preg_match('/^-{2,}(.*)/', $token, $matches)) {
                    $options[] = static::parseOption($matches[1]);
                } else {
                    $arguments[] = static::parseArgument($token);
                }
            }
        }

        return [$arguments, $options];
    }
    protected static function parseArgument(string $token): InputArgument
    {
        [$token, $description] = static::extractDescription($token);

        if (str_contains($token, '=')) {
            [$name, $value] = self::extractValue($token);
            return new InputArgument($name, $description, InputMode::Optional, $value);
        }

        if (str_ends_with($token, "?")) {
            return new InputArgument(trim($token, "?"), $description, InputMode::Optional);
        }

        return new InputArgument($token, $description, InputMode::Required);
    }
    protected static function extractDescription(string $token): array
    {
        $parts = preg_split('/\s+:\s+/', trim($token), 2);

        return count($parts) === 2 ? $parts : [$token, ''];
    }
    protected static function parseOption(string $token): InputOption
    {
        [$token, $description] = static::extractDescription($token);

        $matches = preg_split('/\s*\|\s*/', $token, 2);

        $shortcut = null;

        if (isset($matches[1])) {
            $shortcut = $matches[0];
            $token = $matches[1];
        }

        if (str_contains($token, '=')) {
            [$name, $value] = self::extractValue($token);
            return new InputOption($name, $shortcut, $description, InputMode::Optional, $value);
        } else {
            return new InputOption($token, $shortcut, $description, InputMode::None);
        }
    }

    protected static function extractValue(string $token): array
    {
        $pos = strpos($token, "=");

        $name = substr($token, 0, $pos);

        $value = substr($token, $pos + 1);

        $value = $value != "" ? $value : null;

        return [$name, $value];
    }
}
