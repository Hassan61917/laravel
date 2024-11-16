<?php

namespace Src\Main\Validation;

use Closure;
use Src\Main\Support\MessageBag;

interface IValidator
{
    public function validate(): array;

    public function fails(): bool;

    public function failed(): array;

    public function getErrors(): MessageBag;

    public function getMessages(): MessageBag;

    public function extend(string $rule, Closure $extension, string $message = null): void;

    public function addRule(string $rule, Closure $extension, string $message = null): void;

    public function replaceMessage(string $rule, Closure $replacer): void;
}
