<?php

namespace Src\Main\Validation\Rules;

trait GlobalRule
{
    protected function required(string $attribute, mixed $value, array $params = []): bool
    {
        return !empty($value);
    }
    protected function min(string $attribute, mixed $value, array $params = []): bool
    {
        return strlen($value) >= $params[0];
    }
    protected function max(string $attribute, mixed $value, array $params = []): bool
    {
        return strlen($value) <= $params[0];
    }
}
