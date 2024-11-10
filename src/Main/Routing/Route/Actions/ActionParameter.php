<?php

namespace Src\Main\Routing\Route\Actions;

class ActionParameter
{
    public function __construct(
        public string $name,
        public string $className,
        public mixed $value
    ) {}
}
