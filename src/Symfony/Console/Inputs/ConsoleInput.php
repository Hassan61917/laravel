<?php

namespace Src\Symfony\Console\Inputs;

use InvalidArgumentException;
use RuntimeException;
use Src\Symfony\Console\Inputs\Item\InputItem;

abstract class ConsoleInput implements IConsoleInput
{
    protected array $arguments = [];
    protected array $options = [];

    protected InputItem $item;
    public function __construct(
        protected array $tokens = []
    ) {}
    public function getFirstArgument(): ?string
    {
        foreach ($this->tokens as $token) {
            if (!$this->isOption($token)) {
                return $token;
            }
        }
        return null;
    }
    public function hasParameterOption(string ...$values): bool
    {
        foreach ($this->tokens as $token) {
            foreach ($values as $value) {
                $arg = $this->isLongOption($value) ? $value . "=" : $value;
                if ($token === $value || str_starts_with($token, $arg)) {
                    return true;
                }
            }
        }
        return false;
    }
    public function bind(InputItem $item): void
    {
        $this->arguments = [];

        $this->options = [];

        $this->item = $item;

        $this->parse();
    }
    public function setArgument(string $name, mixed $value): void
    {
        if (!$this->item->hasArgument($name)) {
            throw new InvalidArgumentException("The {$name} argument does not exist.");
        }

        $this->arguments[$name] = $value;
    }
    public function getArgument(string $name): ?string
    {
        if (!$this->item->hasArgument($name)) {
            throw new InvalidArgumentException("The {$name} argument does not exist.");
        }

        return $this->arguments[$name] ?? $this->item->getArgument($name)->getDefaultValue();
    }
    public function hasArgument(string $name): bool
    {
        return $this->item->hasArgument($name);
    }
    public function getArguments(): array
    {
        return array_merge($this->item->getArgumentDefaults(), $this->arguments);
    }
    public function getOption(string $name): ?string
    {
        if (!$this->item->hasOption($name)) {
            throw new InvalidArgumentException("The {$name} option does not exist.");
        }

        return array_key_exists($name, $this->options)
            ? $this->options[$name]
            : $this->item->getOption($name)->getDefaultValue();
    }
    public function optionExists(string $name): bool
    {
        if (!$this->item->hasOption($name)) {
            throw new InvalidArgumentException("The {$name} option does not exist.");
        }

        return array_key_exists($name, $this->options);
    }
    public function setOption(string $name, mixed $value): void
    {
        if (!$this->item->hasOption($name)) {
            throw new InvalidArgumentException("The {$name} option does not exist.");
        }
        $this->options[$name] = $value;
    }
    public function hasOption(string $name): bool
    {
        return $this->item->hasOption($name);
    }
    public function getOptions(): array
    {
        return array_merge($this->item->getOptionDefaults(), $this->options);
    }
    public function validate(): void
    {
        $item = $this->item;

        foreach (array_keys($item->getArguments()) as $argument) {
            if (
                !array_key_exists($argument, $this->arguments)
                && $item->getArgument($argument)->isRequired()
            ) {
                throw new RuntimeException("Not enough arguments (missing: {$argument}).");
            }
        }
    }
    public function escapeToken(string $token): string
    {
        return preg_match('{^[\w-]+$}', $token) ? $token : escapeshellarg($token);
    }
    protected function isOption(string $token): bool
    {
        return $this->isLongOption($token) || $this->isShortOption($token);
    }
    protected function isLongOption(string $token): bool
    {
        return str_starts_with($token, "--");
    }
    protected function isShortOption(string $token): bool
    {
        return $token[0] == "-";
    }
    protected function parseShortOptionSet(string $name): void
    {
        $len = strlen($name);
        for ($i = 0; $i < $len; ++$i) {
            $option = $this->item->getOptionForShortcut($name[$i]);
            if (!$option->acceptValue()) {
                $this->addLongOption($option->getName(), null);
            }
        }
    }
    protected function addArgument(string $name, mixed $value): void
    {
        if (!$this->item->hasArgument($name)) {
            throw new InvalidArgumentException("The $name argument does not exist.");
        }

        $this->arguments[$name] = $value;
    }
    protected function addLongOption(string $name, ?string $value): void
    {
        if (!$this->item->hasOption($name)) {
            throw new RuntimeException("The {$name} option does not exist.");
        }

        $option = $this->item->getOption($name);

        if ($value && !$option->acceptValue()) {
            throw new RuntimeException("The {$name} option does not accept a value.");
        }

        if (!$value && $option->acceptValue()) {
            $value = $this->getValueFromTokens();
        }

        if (!$value && $option->isRequired()) {
            throw new RuntimeException("The {$name} option requires a value.");
        }

        if (!$value && $option->isNone()) {
            $value = true;
        }

        $this->options[$name] = $value;
    }
    protected function addShortOption(string $shortcut, ?string $value = null): void
    {
        $name = $this->item->getOptionForShortcut($shortcut)->getName();

        $this->addLongOption($name, $value);
    }
    protected function getValueFromTokens(): ?string
    {
        return "";
    }
    protected abstract function parse(): void;
}
