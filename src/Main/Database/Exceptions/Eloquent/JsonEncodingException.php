<?php

namespace Src\Main\Database\Exceptions\Eloquent;

use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Exceptions\JsonResource;

class JsonEncodingException extends \RuntimeException
{
    public static function forModel(Model $model, string $message): static
    {
        return new static('Error encoding model [' . get_class($model) . '] with ID [' . $model->getKey() . '] to JSON: ' . $message);
    }
    public static function forAttribute(mixed $model, string $key, string $message): static
    {
        $class = get_class($model);

        return new static("Unable to encode attribute [{$key}] for model [{$class}] to JSON: {$message}.");
    }
}
