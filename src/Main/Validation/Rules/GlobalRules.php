<?php

namespace Src\Main\Validation\Rules;

use InvalidArgumentException;

class GlobalRules extends Rule
{
    use GlobalRule;

    protected string $rule;

    public function setRule(string $rule): static
    {
        $this->rule = $rule;

        return $this;
    }
    public function getRule(): string
    {
        return $this->rule;
    }
    public function pass(string $attribute, mixed $value, array $params = []): bool
    {
        $method = strtolower($this->rule);

        if (!method_exists($this, $method)) {
            throw new InvalidArgumentException("Rule '{$this->rule}' does not exist.");
        }

        if (!$this->$method($attribute, $value, $params)) {
            $this->setMessage($this->rule);
            return false;
        }

        return true;
    }
}
