<?php

namespace Src\Main\Database\Exceptions;

use PDOException;

class QueryException extends PDOException
{
    public function __construct(
        protected string $query,
        protected array $bindings,
        \Exception $exception
    ) {
        parent::__construct($exception->getMessage());
    }
}
