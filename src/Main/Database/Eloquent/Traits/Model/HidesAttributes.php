<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Closure;

trait HidesAttributes
{
    protected array $hidden = [];
    protected array $visible = [];
    public function setHidden(array $hidden): static
    {
        $this->hidden = $hidden;

        return $this;
    }
    public function getHidden(): array
    {
        return $this->hidden;
    }
    public function setVisible(array $visible): static
    {
        $this->visible = $visible;

        return $this;
    }
    public function getVisible(): array
    {
        return $this->visible;
    }
    public function makeVisible(array $attributes): static
    {
        $this->hidden = array_diff($this->hidden, $attributes);

        if (count($this->visible) > 0) {
            $this->visible = array_values(array_unique(array_merge($this->visible, $attributes)));
        }

        return $this;
    }
    public function makeVisibleIf(Closure $condition, array $attributes): static
    {
        return call_user_func($condition, $this) ? $this->makeVisible($attributes) : $this;
    }
    public function makeHidden(array $attributes): static
    {
        $this->hidden = array_values(array_unique(array_merge($this->hidden, $attributes)));

        return $this;
    }
    public function makeHiddenIf(Closure $condition, array $attributes)
    {
        return call_user_func($condition, $this) ? $this->makeHidden($attributes) : $this;
    }
}
