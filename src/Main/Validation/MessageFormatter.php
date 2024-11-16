<?php

namespace Src\Main\Validation;

class MessageFormatter implements IMessageFormatter
{
    public function format(string $message, string $attribute, string $value, array $params = []): string
    {
        $message = str_replace("[v]", $value, $message);

        array_unshift($params, $attribute);

        $index = 0;
        while (str_contains($message, "[") && $index < count($params)) {
            $message = str_replace("[$index]", $params[$index], $message);
            $index++;
        }
        return $message;
    }
}
