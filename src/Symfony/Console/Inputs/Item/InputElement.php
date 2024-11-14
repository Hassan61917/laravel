<?php

namespace Src\Symfony\Console\Inputs\Item;

abstract class InputElement
{
    public function __construct(
        protected string $name,
        protected InputMode $mode = InputMode::Optional,
        protected mixed $defaultValue = null,
    ) {}
    public function getName(): string
    {
        return $this->name;
    }
    public function getMode(): InputMode
    {
        return $this->mode;
    }
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }
    public function isRequired(): bool
    {
        return $this->mode == InputMode::Required;
    }
    public function isOptional(): bool
    {
        return $this->mode == InputMode::Optional;
    }
    public function acceptValue(): bool
    {
        return $this->isRequired() || $this->isOptional();
    }
}
