<?php

namespace Src\Main\Validation\Rules;

interface IRule
{
    public function passes(string $attribute, mixed $value, array $params = []): bool;
    public function getMessage(): string;
}
