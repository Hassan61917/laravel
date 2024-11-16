<?php

namespace Src\Main\Validation;

use Src\Main\Container\IContainer;

class ValidatorFactory implements IValidatorFactory
{
    public function __construct(
        protected IContainer $container,
        protected IMessageHandler $messageHandler
    ) {}
    public function make(array $data, array $rules): IValidator
    {
        $validator = new Validator($data, $rules);
        $validator->setContainer($this->container);
        $validator->setMessageHandler($this->messageHandler);
        return $validator;
    }
}
