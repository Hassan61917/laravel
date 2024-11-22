<?php

namespace Src\Main\Database\Exceptions\Eloquent;

class ClassMorphViolationException extends \RuntimeException
{
    public function __construct(
        public string $model
    ) {
        parent::__construct("No morph map defined for model $this->model.");
    }
}
