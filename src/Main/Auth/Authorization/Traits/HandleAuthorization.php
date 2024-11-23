<?php

namespace Src\Main\Auth\Authorization\Traits;

use Src\Main\Auth\Authorization\Response;

trait HandleAuthorization
{
    public function denyWithStatus(int $status, ?string $message = null, int $code = 0): Response
    {
        return Response::denyWithStatus($status, $message, $code);
    }
    public function denyAsNotFound(?string $message = null, int $code = 0): Response
    {
        return Response::denyWithStatus(404, $message, $code);
    }
    protected function allow(?string $message = null, int $code = 0): Response
    {
        return Response::allow($message, $code);
    }
    protected function deny(?string $message = null, int $code = 0): Response
    {
        return Response::deny($message, $code);
    }
}
