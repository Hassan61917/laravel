<?php

namespace Src\Symfony\Console\Inputs\Item;

class InputArgument extends InputElement
{
    public function __construct(
        string $name,
        protected string $description,
        InputMode $mode = InputMode::Optional,
        mixed $value = null
    ) {
        parent::__construct($name, $mode, $value);
    }
    public function getDescription(): string
    {
        return $this->description;
    }
}
