<?php

namespace Src\Main\Validation;

use Src\Main\Validation\Rules\IRule;

interface IMessageHandler
{
    public function handle(IRule $rule, string $attribute, string $value, array $params): string;
}
