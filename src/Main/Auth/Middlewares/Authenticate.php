<?php

namespace Src\Main\Auth\Middlewares;

use Closure;
use Src\Main\Auth\Authentication\IGuard;
use Src\Main\Auth\Exceptions\AuthenticationException;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;

class Authenticate extends Middleware
{
    protected static Closure $redirectToCallback;
    public function __construct(
        protected IGuard $auth
    ) {}
    public static function using(string $guard, string ...$others): string
    {
        return static::class . ':' . implode(',', [$guard, ...$others]);
    }
    public static function redirectUsing(callable $redirectToCallback): void
    {
        static::$redirectToCallback = $redirectToCallback;
    }
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        return $this->authenticate($request, $args);
    }
    protected function authenticate(Request $request, array $guards): Response
    {
        if (!$this->auth->check()) {
            $this->unauthenticated($request, $guards);
        }

        return $this->handleNext($request, ...$guards);
    }
    protected function unauthenticated(Request $request, array $guards): void
    {
        throw new AuthenticationException(
            'Unauthenticated.',
            $guards,
            $request->expectsJson() ? null : $this->redirectTo($request),
        );
    }
    protected function redirectTo(Request $request): ?string
    {
        if (static::$redirectToCallback) {
            return call_user_func(static::$redirectToCallback, $request);
        }

        return null;
    }
}
