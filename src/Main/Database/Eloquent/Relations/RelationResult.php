<?php

namespace Src\Main\Database\Eloquent\Relations;

use Illuminate\Support\Collection;
use Src\Main\Database\Eloquent\Model;

class RelationResult
{
    protected Model $item;

    protected Collection $items;
    protected Pivot $pivot;
    public function setItems(Collection $items): static
    {
        $this->items = $items;

        return $this;
    }
    public function setItem(Model $model): static
    {
        $this->item = $model;

        return $this;
    }
    public function setPivot(Pivot $pivot): static
    {
        $this->pivot = $pivot;

        return $this;
    }
    public function hasItem(): bool
    {
        return isset($this->item);
    }
    public function hasItems(): bool
    {
        return isset($this->items);
    }
    public function hasPivot(): bool
    {
        return isset($this->pivot);
    }
    public function getItem(): Model
    {
        return $this->item;
    }
    public function getItems(): Collection
    {
        return $this->items;
    }
    public function getPivot(): Pivot
    {
        return $this->pivot;
    }
}
