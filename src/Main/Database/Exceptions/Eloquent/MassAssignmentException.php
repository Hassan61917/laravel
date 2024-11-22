<?php

namespace Src\Main\Database\Exceptions\Eloquent;

use Src\Main\Database\Eloquent\Model;

class MassAssignmentException extends \Exception
{
    public function __construct(array $keys, Model $model)
    {
        parent::__construct($this->createMessage($keys, get_class($model)));
    }
    protected function createMessage(array $keys, string $class): string
    {
        $keys = implode(",", $keys);

        return "Add $keys to fillable properties for creating $class";
    }
}
