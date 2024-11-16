<?php

namespace Src\Main\Validation;

interface IValidatorFactory
{
    public function make(array $data, array $rules): IValidator;
}
