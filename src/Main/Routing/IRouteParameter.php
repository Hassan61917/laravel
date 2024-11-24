<?php

namespace Src\Main\Routing;

use Src\Main\Database\Eloquent\Model;

interface IRouteParameter
{
    public function getRouteKey(): int;
    public function getRouteKeyName(): string;
    public function resolveRouteBinding(string $value, string $field = null): ?Model;
}
