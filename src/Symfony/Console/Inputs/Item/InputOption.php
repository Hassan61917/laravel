<?php

namespace Src\Symfony\Console\Inputs\Item;

use InvalidArgumentException;

class InputOption extends InputArgument
{
    public function __construct(
        string $name,
        protected ?string $shortcut,
        string $description,
        InputMode $mode = InputMode::None,
        mixed $defaultValue = null
    ) {
        parent::__construct($this->setName($name), $description, $mode, $defaultValue);
    }
    public function getShortcut(): ?string
    {
        return $this->shortcut;
    }
    public function hasShortcut(): bool
    {
        return $this->shortcut != null;
    }
    public function equals(InputOption $option): bool
    {
        return $option->getName() === $this->getName()
            && $option->getShortcut() === $this->getShortcut()
            && $option->isRequired() === $this->isRequired()
            && $option->isOptional() === $this->isOptional();
    }
    protected function setName(string $name): string
    {
        if (empty($name)) {
            throw new InvalidArgumentException('An option name cannot be empty.');
        }

        if (str_starts_with($name, '--')) {
            $name = substr($name, 2);
        }

        return $this->name = $name;
    }
}
