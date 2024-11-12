<?php

namespace Src\Main\Cookie\Middlewares;

use Src\Main\Cookie\CookieValuePrefix;

use Src\Main\Encryption\Exceptions\DecryptException;
use Src\Main\Encryption\IEncryptor;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Symfony\Http\Cookie;

class EncryptCookies extends Middleware
{
    protected static array $neverEncrypt = [];
    protected static bool $serialize = false;
    protected array $except = [];
    public static function serialized(string $name): bool
    {
        return static::$serialize;
    }
    public function __construct(
        protected IEncryptor $encryptor
    ) {}
    public function disableFor(string ...$names): void
    {
        $this->except = array_merge($this->except, $names);
    }
    public function isDisabled(string $name): bool
    {
        return in_array($name, array_merge($this->except, static::$neverEncrypt));
    }
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        $response = $this->handleNext($this->decrypt($request), ...$args);

        return $this->encrypt($response);
    }
    protected function decrypt(Request $request): Request
    {
        $cookies = $request->getCookies();

        foreach ($cookies->all() as $key => $cookie) {
            if (!$this->isDisabled($key)) {
                try {
                    $value = $this->decryptCookie($key, $cookie);
                    $cookies->set($key, $this->validateValue($key, $value));
                } catch (DecryptException) {
                    $cookies->set($key, null);
                }
            }
        }

        return $request;
    }
    protected function encrypt(Response $response): Response
    {
        $headers = $response->getHeaders();

        foreach ($headers->getCookies() as $cookie) {
            if (!$this->isDisabled($cookie->getName())) {

                $value = CookieValuePrefix::create(
                    $cookie->getName(),
                    $this->encryptor->getKey()
                );

                $headers->setCookie($this->duplicate(
                    $cookie,
                    $this->encryptor->encrypt(
                        $value . $cookie->getValue(),
                        static::serialized($cookie->getName())
                    )
                ));
            }
        }

        return $response;
    }
    protected function decryptCookie(string $name, string $cookie): string
    {
        return $this->encryptor->decrypt($cookie, static::serialized($name));
    }
    protected function validateValue(string $key, string $value): string
    {
        return CookieValuePrefix::validate($key, $value, [$this->encryptor->getKey()]);
    }
    protected function duplicate(Cookie $cookie, string $value): Cookie
    {
        return $cookie->withValue($value);
    }
}
