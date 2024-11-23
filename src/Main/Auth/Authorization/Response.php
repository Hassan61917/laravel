<?php

namespace Src\Main\Auth\Authorization;

use Illuminate\Contracts\Support\Arrayable;
use Src\Main\Auth\Exceptions\AuthorizationException;
use Stringable;

class Response implements Arrayable, Stringable
{
    protected int $status = 403;
    public function __construct(
        protected bool $allowed,
        protected ?string $message = null,
        protected int $code = 0
    ) {}
    public static function allow(?string $message = null, int $code = 0): static
    {
        return new static(true, $message, $code);
    }
    public static function deny(?string $message = null, int $code = 0): static
    {
        return new static(false, $message, $code);
    }
    public static function denyWithStatus(int $status, ?string $message = null, int $code = 0): static
    {
        return static::deny($message, $code)->withStatus($status);
    }
    public static function denyAsNotFound(?string $message = null, int $code = 0): Response
    {
        return static::denyWithStatus(404, $message, $code);
    }
    public function allowed(): bool
    {
        return $this->allowed;
    }
    public function denied(): bool
    {
        return ! $this->allowed();
    }
    public function message(): ?string
    {
        return $this->message;
    }
    public function code(): int
    {
        return $this->code;
    }
    public function authorize(): static
    {
        if ($this->denied()) {
            $exception = new AuthorizationException($this->message(), $this->code());

            $exception
                ->setResponse($this)
                ->withStatus($this->status);

            throw $exception;
        }

        return $this;
    }
    public function withStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }
    public function asNotFound(): static
    {
        return $this->withStatus(404);
    }
    public function status(): int
    {
        return $this->status;
    }
    public function toArray(): array
    {
        return [
            'allowed' => $this->allowed(),
            'message' => $this->message(),
            'code' => $this->code(),
        ];
    }
    public function __toString(): string
    {
        return $this->message();
    }
}
