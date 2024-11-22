<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Src\Main\Utils\Str;

trait HasMutator
{
    public function hasSetMutator(string $key): bool
    {
        return $this->hasMutator($key, "set");
    }
    public function hasGetMutator(string $key): bool
    {
        return $this->hasMutator($key, "get");
    }
    protected function runSetMutator(string $key, mixed $value): mixed
    {
        return $this->runMutator($key, "set", $value);
    }
    protected function runGetMutator(string $key, mixed $value): mixed
    {
        return $this->runMutator($key, "get", $value);
    }
    protected function hasMutator(string $key, string $prefix): bool
    {
        return method_exists($this, $this->getMutatorName($key, $prefix));
    }
    protected function getMutatorName(string $key, string $prefix): string
    {
        return $prefix . Str::studly($key) . 'Attribute';
    }
    protected function runMutator(string $key, string $prefix, mixed $value): mixed
    {
        $method = $this->getMutatorName($key, $prefix);

        return $this->$method($value);
    }
}
