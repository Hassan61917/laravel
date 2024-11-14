<?php

namespace Src\Symfony\Console\Inputs\Item;

use InvalidArgumentException;
use LogicException;

class InputItem
{
    protected array $arguments = [];
    protected array $options = [];
    protected array $shortcuts = [];
    protected array $usedArguments = [];
    protected int $requiredCount = 0;
    public function __construct(array $arguments = [], array $options = [])
    {
        $this->setArguments(...$arguments);
        $this->setOptions(...$options);
    }
    public function setArguments(InputArgument ...$arguments): void
    {
        $this->arguments = [];

        $this->requiredCount = 0;

        $this->addArguments(...$arguments);
    }
    public function addArguments(InputArgument ...$arguments): void
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }
    public function addArgument(InputArgument $argument): void
    {
        if ($this->hasArgument($argument->getName())) {
            throw new LogicException("Argument {$argument->getName()} already exists.");
        }

        if ($argument->isRequired()) {
            $this->requiredCount++;
        }

        $this->arguments[$argument->getName()] = $argument;
    }
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]);
    }
    public function getArgument(string $name): ?InputArgument
    {
        return $this->arguments[$name] ?? null;
    }
    public function getArguments(): array
    {
        return $this->arguments;
    }
    public function getArgumentCount(): int
    {
        return count($this->arguments);
    }
    public function getRequiredCount(): int
    {
        return $this->requiredCount;
    }
    public function getArgumentDefaults(): array
    {
        $values = [];

        foreach ($this->arguments as $argument) {
            $values[$argument->getName()] = $argument->getDefaultValue();
        }

        return $values;
    }
    public function setOptions(InputOption ...$options): void
    {
        $this->options = [];

        $this->shortcuts = [];

        $this->addOptions(...$options);
    }
    public function addOptions(InputOption ...$options): void
    {
        foreach ($options as $option) {
            $this->addOption($option);
        }
    }
    public function addOption(InputOption $option): void
    {
        $name = $option->getName();

        if ($this->hasOption($name) && !$option->equals($this->options[$name])) {
            throw new LogicException("Option {$name} already exists.");
        }

        if ($option->hasShortcut()) {
            $this->addShortcut($option);
        }

        $this->options[$name] = $option;
    }
    public function getOption(string $name): InputOption
    {
        if (!$this->hasOption($name)) {
            throw new InvalidArgumentException("The {$name} option does not exist.");
        }

        return $this->options[$name];
    }
    public function hasOption(string $name): bool
    {
        return isset($this->options[$name]);
    }
    public function getOptions(): array
    {
        return $this->options;
    }
    public function hasShortcut(string $name): bool
    {
        return isset($this->shortcuts[$name]);
    }
    public function getShortcuts(): array
    {
        return $this->shortcuts;
    }
    public function getOptionForShortcut(string $shortcut): InputOption
    {
        return $this->getOption($this->shortcutToName($shortcut));
    }
    public function getOptionDefaults(): array
    {
        $values = [];
        foreach ($this->options as $option) {
            $values[$option->getName()] = $option->getDefaultValue();
        }

        return $values;
    }
    public function shortcutToName(string $shortcut): string
    {
        if (!$this->hasShortcut($shortcut)) {
            throw new InvalidArgumentException("The {$shortcut} option does not exist.");
        }

        return $this->getShortcutValue($shortcut);
    }
    public function popArgument(): ?InputArgument
    {
        foreach (array_reverse($this->arguments) as $name => $argument) {
            if (!in_array($name, $this->usedArguments)) {
                $this->usedArguments[] = $name;
                return $argument;
            }
        }
        return null;
    }
    public function clone(): static
    {
        $item = new InputItem();
        $item->setArguments(...$this->getArguments());
        $item->setOptions(...$this->getOptions());
        return $item;
    }
    protected function addShortcut(InputOption $option): void
    {
        $shortcut = $option->getShortcut();

        if (
            $this->hasShortcut($shortcut) &&
            !$option->equals($this->options[$this->getShortcutValue($shortcut)])
        ) {
            throw new LogicException("An option with shortcut {$shortcut} already exists.");
        }

        $this->shortcuts[$shortcut] = $option->getName();
    }
    protected function getShortcutValue(string $shortcut): string
    {
        return $this->shortcuts[$shortcut];
    }
    public function getSynopsis(): string
    {
        $elements = [];

        foreach ($this->getOptions() as $option) {
            $value = '';
            if ($option->acceptValue()) {
                $value = sprintf(
                    ' %s%s%s',
                    $option->isOptional() ? '[' : '',
                    strtoupper($option->getName()),
                    $option->isOptional() ? ']' : ''
                );
            }

            $shortcut = $option->getShortcut() ? sprintf('-%s|', $option->getShortcut()) : '';
            $elements[] = sprintf('[%s--%s%s]', $shortcut, $option->getName(), $value);
        }

        if (count($elements) && $this->getArguments()) {
            $elements[] = '[--]';
        }

        $tail = '';

        foreach ($this->getArguments() as $argument) {
            $element = '<' . $argument->getName() . '>';


            if (!$argument->isRequired()) {
                $element = '[' . $element;
                $tail .= ']';
            }

            $elements[] = $element;
        }

        return implode(' ', $elements) . $tail;
    }
}
