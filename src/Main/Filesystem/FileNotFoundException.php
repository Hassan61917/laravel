<?php

namespace Src\Main\Filesystem;

use Exception;

class FileNotFoundException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
