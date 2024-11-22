<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

trait HasUniqueIds
{
    public bool $usesUniqueIds = false;
    public function usesUniqueIds(): bool
    {
        return $this->usesUniqueIds;
    }
    public function setUniqueIds(): void
    {
        foreach ($this->uniqueIds() as $column) {
            if (empty($this->{$column})) {
                $this->{$column} = $this->newUniqueId();
            }
        }
    }
    public function newUniqueId(): ?string
    {
        return null;
    }
    public function uniqueIds(): array
    {
        return [];
    }
}
