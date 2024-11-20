<?php

namespace Src\Main\Database\Schema\Blueprint;

use Src\Main\Database\Schema\Fluent;

class ColumnDefinition extends Fluent
{
    public function reset(string $key): void
    {
        $this->attributes[$key] = null;
    }
}
