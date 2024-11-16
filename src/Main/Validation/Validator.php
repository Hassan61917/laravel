<?php

namespace Src\Main\Validation;

use Closure;
use Src\Main\Container\IContainer;
use Src\Main\Support\MessageBag;
use Src\Main\Validation\Rules\ClosureRule;
use Src\Main\Validation\Rules\GlobalRules;
use Src\Main\Validation\Rules\IRule;

class Validator implements IValidator
{
    protected array $failedRules = [];
    protected array $extensions = [];
    protected array $addedRules = [];
    protected array $rules = [];
    protected array $replaceMessages = [];
    protected array $addedMessages = [];
    protected MessageBag $messages;
    protected IContainer $container;
    protected IMessageHandler $messageHandler;
    public function __construct(
        protected array $data = [],
        array $rules = [],
    ) {
        $this->addRules($rules);
    }
    public function setContainer(IContainer $container): static
    {
        $this->container = $container;

        return $this;
    }
    public function setMessageHandler(IMessageHandler $IMessageHandler): static
    {
        $this->messageHandler = $IMessageHandler;

        return $this;
    }
    public function addRules(array $rules): void
    {
        $parser = new ValidationRuleParser($this->data);

        $rules  = $parser->explodeRules($rules);

        $this->rules = array_merge_recursive($this->rules, $rules);
    }
    public function getRules(): array
    {
        return $this->rules;
    }
    public function getErrors(): MessageBag
    {
        return $this->messages;
    }
    public function getMessages(): MessageBag
    {
        if (!isset($this->messages)) {
            $this->passes();
        }

        return $this->messages;
    }
    public function fails(): bool
    {
        return ! $this->passes();
    }
    public function failed(): array
    {
        return $this->failedRules;
    }
    public function extend(string $rule, Closure $extension, string $message = null): void
    {
        $this->extensions[$rule] = $extension;

        $this->addMessage($rule, $message);
    }
    public function addRule(string $rule, Closure $extension, string $message = null): void
    {
        $this->extend($rule, $extension, $message);

        $this->addedRules[$rule] = $extension;
    }
    public function replaceMessage(string $rule, Closure $replacer): void
    {
        $this->replaceMessages[$rule] = $replacer;
    }
    public function validate(): array
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }

        if ($this->messages->isNotEmpty()) {
            return $this->messages->toArray();
        }

        return $this->data;
    }
    public function passes(): bool
    {
        $this->messages = new MessageBag();
        $this->failedRules = [];
        foreach ($this->rules as $attribute => $rules) {
            foreach ($rules as $rule) {
                [$rule, $parameters] = ValidationRuleParser::parse($rule);

                $value = $this->getValue($attribute);

                if ($this->isValidatable($rule, $attribute, $value)) {
                    $this->validateAttribute($attribute, $rule, $value, $parameters);
                }
            }
        }

        return $this->messages->isEmpty();
    }
    protected function addMessage(string $key, string $message = null): void
    {
        if ($message) {
            $this->addedMessages[$key] = $message;
        }
    }
    protected function getValue(string $attribute): mixed
    {
        return $this->data[$attribute] ?? null;
    }
    protected function isValidatable(string|IRule $rule, string $attribute, mixed $value): bool
    {
        if (!isset($this->data[$attribute])) {
            return false;
        }
        if (is_string($rule) && empty($value) && in_array($rule, ["sometimes", "nullable"])) {
            return false;
        }
        return true;
    }
    protected function validateAttribute(string $attribute, string|IRule $rule, mixed $value, array $params): void
    {
        $rule = is_string($rule) ? $this->findRule($rule) : $rule;

        if (!$rule->passes($attribute, $value, $params)) {
            $this->failedRule($rule, $attribute, $value, $params);
        }
    }
    protected function findRule(string $rule): ?IRule
    {
        if (class_exists($rule)) {
            return $this->container->make($rule);
        }

        if (in_array($rule, $this->addedRules)) {
            return new ClosureRule($this->addedRules[$rule]);
        }

        return $this->container
            ->make("globalRules")
            ->setRule($rule);
    }
    protected function failedRule(IRule $rule, string $attribute, mixed $value, array $parameters): void
    {
        $ruleClass = $rule instanceof GlobalRules ? $rule->getRule() : get_class($rule);

        $this->addFailedRule($attribute, $ruleClass, $parameters);

        $message = $this->messageHandler->handle($rule, $attribute, $value, $parameters);

        $message = $this->callReplacer($message, $attribute, $ruleClass, $parameters);

        $this->messages->add($attribute, $message);
    }
    protected function addFailedRule(string $attribute, string $rule, array $parameters): void
    {
        $this->failedRules[$attribute][$rule] = $parameters;
    }
    protected function callReplacer(string $message, string $attribute, string $rule, array $parameters): string
    {
        if (!isset($this->replaceMessages[$rule])) {
            return $message;
        }

        $callback = $this->replaceMessages[$rule];

        return $callback(...func_get_args());
    }
}
