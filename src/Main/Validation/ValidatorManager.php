<?php

namespace Src\Main\Validation;

use Closure;

class ValidatorManager
{
    protected array $extensions = [];
    protected array $addedRules = [];
    protected array $fallbackMessages = [];
    protected array $replaceMessages = [];
    public function __construct(
        protected IValidatorFactory $validatorFactory,
    ) {}
    public function make(array $data, array $rules): IValidator
    {
        $validator = $this->validatorFactory->make($data, $rules);

        $this->initValidator($validator);

        return $validator;
    }
    public function validate(array $data, array $rules): array
    {
        return $this->make($data, $rules)->validate();
    }
    public function extend(string $rule, Closure $extension, string $message = null): static
    {
        $this->extensions[$rule] = $extension;

        $this->addMessage($rule, $message);

        return $this;
    }
    public function extendImplicit(string $rule, Closure $extension, string $message = null): static
    {
        $this->addedRules[$rule] = $extension;

        $this->addMessage($rule, $message);

        return $this;
    }
    public function replaceMessage(string $rule, Closure $replacer): static
    {
        $this->replaceMessages[$rule] = $replacer;

        return $this;
    }
    public function setFactory(IValidatorFactory $factory): static
    {
        $this->validatorFactory = $factory;

        return $this;
    }
    protected function initValidator(IValidator $validator): void
    {
        foreach ($this->extensions as $rule => $extension) {
            $validator->extend($rule, $extension, $this->getMessage($rule));
        }

        foreach ($this->addedRules as $rule => $extension) {
            $validator->addRule($rule, $extension, $this->getMessage($rule));
        }

        foreach ($this->replaceMessages as $rule => $message) {
            $validator->replaceMessage($rule, $message);
        }
    }
    protected function getMessage(string $key): string
    {
        return $this->fallbackMessages[$key];
    }
    protected function addMessage(string $key, string $message = null): static
    {
        if ($message) {
            $this->fallbackMessages[$key] = $message;
        }

        return $this;
    }
}
