<?php

namespace Src\Main\Http\Exceptions;

class ServerErrorException extends HttpException
{
    public function __construct(string $message = "Something went wrong", array $headers = [])
    {
        parent::__construct($message, 500, $headers);
    }
}
