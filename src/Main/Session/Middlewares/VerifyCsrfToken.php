<?php

namespace Src\Main\Session\Middlewares;

use Src\Main\Cookie\CookieValuePrefix;
use Src\Main\Cookie\Middlewares\EncryptCookies;
use Src\Main\Encryption\Encryptor;
use Src\Main\Encryption\Exceptions\DecryptException;
use Src\Main\Foundation\Application;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Session\TokenMismatchException;
use Src\Main\Support\Traits\InteractsWithTime;
use Src\Symfony\Http\Cookie;

class VerifyCsrfToken extends Middleware
{
    use InteractsWithTime;
    protected static array $neverVerify = [];

    protected array $except = [];
    protected bool $addHttpCookie = true;
    public function __construct(
        protected Application $app,
        protected Encryptor $encryptor,
    ) {}
    public static function serialized(): bool
    {
        return EncryptCookies::serialized('XSRF-TOKEN');
    }
    public function shouldAddXsrfTokenCookie()
    {
        return $this->addHttpCookie;
    }
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        if (
            $this->isReading($request) ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            $response = $this->handleNext($request, ...$args);

            if ($this->shouldAddXsrfTokenCookie()) {
                $this->addCookieToResponse($request, $response);
            }

            return $response;
        }

        throw new TokenMismatchException('CSRF token mismatch.');
    }
    public function getExcludedPaths(): array
    {
        return $this->except ?? [];
    }
    protected function isReading(Request $request): bool
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }
    protected function inExceptArray(Request $request): bool
    {
        foreach ($this->getExcludedPaths() as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        return false;
    }
    protected function tokensMatch(Request $request): bool
    {
        $token = $this->getTokenFromRequest($request);

        return is_string($request->session()->token()) &&
            is_string($token) &&
            hash_equals($request->session()->token(), $token);
    }
    protected function getTokenFromRequest(Request $request)
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

        $header = $request->header('X-XSRF-TOKEN');

        if (! $token && $header) {
            try {
                $token = CookieValuePrefix::remove(
                    $this->encryptor->decrypt($header, static::serialized())
                );
            } catch (DecryptException) {
                $token = '';
            }
        }

        return $token;
    }
    protected function addCookieToResponse(Request $request, Response $response): Response
    {
        $config = config('session');

        $response->getHeaders()->setCookie(
            $this->newCookie($request, $config)
        );

        return $response;
    }
    protected function newCookie(Request $request, array $config): Cookie
    {
        return new Cookie(
            'XSRF-TOKEN',
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );
    }
}
