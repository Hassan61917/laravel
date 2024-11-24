<?php

namespace Src\Main\Http\Exceptions;

use Src\Main\Auth\Exceptions\AuthorizationException;
use Src\Main\Http\Response;

class AccessDeniedException extends HttpException
{
    public function __construct(
        protected AuthorizationException $exception
    ) {
        parent::__construct($this->buildMessage(), $this->buildStatusCode());
    }
    protected function buildMessage(): string
    {
        $e = $this->exception;

        if (!$e->hasStatus()) {
            return $e->getMessage();
        }

        return $e->response()?->message()
            ?: (Response::$statusTexts[$e->status()] ?? 'Whoops, looks like something went wrong.');
    }
    protected function buildStatusCode(): int
    {
        return $this->exception->hasStatus() ? $this->exception->status() : 403;
    }
}
