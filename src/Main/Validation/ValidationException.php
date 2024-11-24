<?php

namespace Src\Main\Validation;

use Src\Main\Facade\Facades\Translator;
use Src\Main\Http\Response;
use Src\Main\Support\MessageBag;

class ValidationException extends \Exception
{
    protected Response $response;
    protected int $statusCode = 422;
    public string $errorBag;
    public string $redirectTo;
    public function __construct(
        protected IValidator $validator,
    ) {
        parent::__construct($this->summarize($this->validator));
    }
    public function errors(): MessageBag
    {
        return $this->validator->getErrors();
    }
    public function status(int $status): static
    {
        $this->statusCode = $status;

        return $this;
    }
    public function errorBag(string $errorBag): static
    {
        $this->errorBag = $errorBag;

        return $this;
    }
    public function redirectTo(string $url): static
    {
        $this->redirectTo = $url;

        return $this;
    }
    public function getResponse(): Response
    {
        return $this->response;
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    protected function summarize(IValidator $validator)
    {
        $messages = $validator->getErrors()->all();

        if (empty($messages)) {
            return Translator::get("validation:default");
        }

        return array_shift($messages);
    }
}
