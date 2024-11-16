<?php

namespace Src\Main\Validation;

use Src\Main\Translation\ITranslator;
use Src\Main\Validation\Rules\IRule;

class MessageHandler implements IMessageHandler
{
    public function __construct(
        protected ITranslator $translator,
        protected IMessageFormatter $messageFormatter
    ) {}

    public function handle(IRule $rule, string $attribute, mixed $value, array $params): string
    {
        $attribute = $this->findAttribute($attribute);

        $message = $this->findMessage($rule->getMessage());

        return $this->formatMessage($message, $attribute, $value, $params);
    }
    protected function findAttribute(string $attribute): string
    {
        $key = "validation:attributes.$attribute";
        $result = $this->translate($key);
        return $key == $result ? $attribute : $result;
    }
    protected function translate(string $key): string
    {
        return $this->translator->get($key, env("APP_LANGUAGE"));
    }
    protected function findMessage(string $message): string
    {
        $keys = $this->createKeys($message);

        return $this->getMessage($keys) ?? $message;
    }
    protected function createKeys(string $rule): array
    {
        return ["validation:{$rule}", $rule];
    }
    protected function getMessage(array $keys): ?string
    {
        foreach ($keys as $key) {
            $result = $this->translate($key);
            if ($result !== $key) {
                return $result;
            }
        }
        return null;
    }
    protected function formatMessage(string $message, string $attribute, string $value, $params): string
    {
        return $this->messageFormatter->format($message, $attribute, $value, $params);
    }
}
