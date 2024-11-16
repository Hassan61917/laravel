<?php

namespace Src\Main\Validation\Rules;

abstract class Rule implements IRule
{
    protected array $params = [];
    protected string $message = "";

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }
    public function getParams(): array
    {
        return $this->params;
    }
    public function passes(string $attribute, mixed $value, array $params = []): bool
    {
        $this->setParams($params);
        try {
            return $this->pass($attribute, $value, $params);
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage());
        }
        return false;
    }
    public abstract function pass(string $attribute, mixed $value, array $params = []): bool;
}
