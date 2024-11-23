<?php

namespace Src\Main\Auth\Exceptions;

use Closure;
use Src\Main\Http\Request;

class AuthenticationException extends \Exception
{
    protected static Closure $redirectToCallback;
    public function __construct(
        string $message = 'Unauthenticated.',
        protected array $guards = [],
        protected ?string $redirectTo = null
    ) {
        parent::__construct($message);
    }
    public static function redirectUsing(callable $redirectToCallback): void
    {
        static::$redirectToCallback = $redirectToCallback;
    }
    public function guards(): array
    {
        return $this->guards;
    }
    public function redirectTo(Request $request): ?string
    {
        if ($this->redirectTo) {
            return $this->redirectTo;
        }

        if (static::$redirectToCallback) {
            return call_user_func(static::$redirectToCallback, $request);
        }
        return null;
    }
}
