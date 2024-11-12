<?php

namespace Src\Main\Cookie\Middlewares;

use Src\Main\Cookie\CookieJar;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;

class AddQueuedCookiesToResponse extends Middleware
{
    public function __construct(
        protected CookieJar $cookies
    ) {}
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        $response = $this->next->handle($request, ...$args);

        foreach ($this->cookies->getQueuedCookies() as $cookie) {
            $response->getHeaders()->setCookie($cookie);
        }

        return $response;
    }
}
