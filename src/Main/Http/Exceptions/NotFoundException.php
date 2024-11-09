<?php

namespace Src\Main\Http\Exceptions;

class NotFoundException extends HttpException
{
    public function __construct(string $message = "not Found", array $headers = [])
    {
        parent::__construct($message, 404, $headers);
    }
}
