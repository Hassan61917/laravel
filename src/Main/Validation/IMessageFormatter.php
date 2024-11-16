<?php

namespace Src\Main\Validation;

interface IMessageFormatter
{
    public function format(string $message, string $attribute, string $value, array $params = []): string;
}
