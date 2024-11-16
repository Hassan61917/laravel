<?php

namespace Src\Main\Validation;

use Closure;
use InvalidArgumentException;
use Src\Main\Validation\Rules\ClosureRule;
use Src\Main\Validation\Rules\IRule;

class ValidationRuleParser
{
    public function __construct(
        protected array $data
    ) {}
    public static function parse(string|object $rule): array
    {
        if ($rule instanceof IRule) {
            return [$rule, []];
        }

        return static::parseRule($rule);
    }
    protected static function parseRule(string $rule): array
    {
        $parameters = [];

        if (str_contains($rule, ':')) {
            [$rule, $parameter] = explode(':', $rule, 2);

            $parameters = explode(",", $parameter);
        }

        return [$rule, $parameters];
    }
    public function explodeRules(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            $rules[$key] = array_unique($this->explodeRule($rule));
        }
        return $rules;
    }
    protected function explodeRule(object|string|array $rule): array
    {
        $result = $rule;

        if (is_string($result)) {
            $result = explode('|', $rule);
        }

        if (is_object($result)) {
            $result = [$rule];
        }

        return $this->filterRules($result);
    }
    protected function filterRules(array $rules): array
    {
        $result = [];

        foreach ($rules as $key => $value) {
            if ($value instanceof Closure) {
                $value = new ClosureRule($value);
            }

            if (is_object($value) && !($value instanceof IRule)) {
                $className = get_class($value);
                throw new InvalidArgumentException("{$className} is not a valid rule");
            }

            $result[$key] = $value;
        }
        return $result;
    }
}
