<?php

namespace Src\Main\Database\Exceptions\Eloquent;

use OutOfBoundsException;
use Src\Main\Database\Eloquent\Model;

class MissingAttributeException extends OutOfBoundsException
{
    public function __construct(Model $model, string $key)
    {
        parent::__construct($this->parseMessage($model, $key));
    }
    protected function parseMessage(Model $model, string $key): string
    {
        $class = get_class($model);

        return "The attribute {$key} either does not exist or was not retrieved for model {$class}.";
    }
}
