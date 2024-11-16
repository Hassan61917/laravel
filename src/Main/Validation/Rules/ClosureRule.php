<?php

namespace Src\Main\Validation\Rules;

class ClosureRule extends Rule
{
    public function __construct(
        protected \Closure $closure
    ) {}
    public function pass(string $attribute, mixed $value, array $params = []): bool
    {
        return call_user_func($this->closure, $attribute, $value, $params);
    }
}
