<?php

namespace Src\Main\Auth\Exceptions;

use Exception;
use Src\Main\Auth\Authorization\Response;
use Throwable;

class AuthorizationException extends Exception
{
    protected Response $response;

    protected int $status = 0;
    public function __construct(
        string $message = null,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message ?? 'This action is unauthorized.', $code, $previous);
    }
    public function setResponse(Response $response): static
    {
        $this->response = $response;

        return $this;
    }
    public function response(): Response
    {
        return $this->response;
    }
    public function withStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }
    public function status(): int
    {
        return $this->status;
    }
    public function asNotFound(): static
    {
        return $this->withStatus(404);
    }
    public function hasStatus(): bool
    {
        return $this->status > 0;
    }
    public function toResponse(): Response
    {
        return Response::deny($this->message, $this->code)->withStatus($this->status);
    }
}
