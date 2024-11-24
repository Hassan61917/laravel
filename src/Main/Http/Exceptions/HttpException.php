<?php

namespace Src\Main\Http\Exceptions;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(
        string $message,
        protected int $statusCode = 500,
        protected array $headers = []
    ) {
        parent::__construct($message);
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
