<?php

namespace Src\Main\Database\Schema;

class ForeignKeyDefinition extends Fluent
{
    public function cascadeOnUpdate(): static
    {
        return $this->onUpdate('cascade');
    }
    public function restrictOnUpdate(): static
    {
        return $this->onUpdate('restrict');
    }
    public function noActionOnUpdate(): static
    {
        return $this->onUpdate('no action');
    }
    public function cascadeOnDelete(): static
    {
        return $this->onDelete('cascade');
    }
    public function restrictOnDelete(): static
    {
        return $this->onDelete('restrict');
    }
    public function nullOnDelete(): static
    {
        return $this->onDelete('set null');
    }
    public function noActionOnDelete(): static
    {
        return $this->onDelete('no action');
    }
}
