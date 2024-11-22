<?php

namespace Src\Main\Database\Eloquent\Traits\Model;

use Carbon\Carbon;

trait HasTimestamps
{
    public bool $timestamps = true;
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    public function setCreatedAt(mixed $value): static
    {
        $this->{$this->getCreatedAtColumn()} = $value;

        return $this;
    }
    public function getCreatedAtColumn(): string
    {
        return static::CREATED_AT;
    }
    public function setUpdatedAt(mixed $value): static
    {
        $this->{$this->getUpdatedAtColumn()} = $value;

        return $this;
    }
    public function getUpdatedAtColumn(): string
    {
        return static::UPDATED_AT;
    }
    public function usesTimestamps(): bool
    {
        return $this->timestamps;
    }
    public function updateTimestamps(): static
    {
        $time = $this->freshTimestamp();

        $updatedAtColumn = $this->getUpdatedAtColumn();

        if (! $this->isDirty([$updatedAtColumn])) {
            $this->setUpdatedAt($time);
        }

        $createdAtColumn = $this->getCreatedAtColumn();

        if (! $this->exists && ! $this->isDirty([$createdAtColumn])) {
            $this->setCreatedAt($time);
        }

        return $this;
    }
    public function freshTimestamp(): Carbon
    {
        return Carbon::now();
    }
    public function freshTimestampString(): string
    {
        return $this->fromDateTime($this->freshTimestamp());
    }
    protected function updateTimestampColumns(): array
    {
        if (!$this->usesTimestamps()) {
            return [];
        }

        $result = [];

        $time = $this->freshTimestamp();

        if (!$this->exists) {
            $this->setCreatedAt($time);

            $result[$this->getCreatedAtColumn()] = $this->fromDateTime($time);
        }

        $this->setUpdatedAt($time);

        $result[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);

        return $result;
    }
    public function touch(?string $attribute = null): bool
    {
        if ($attribute) {
            $this->$attribute = $this->freshTimestamp();

            return $this->save();
        }

        if (! $this->usesTimestamps()) {
            return false;
        }

        $this->updateTimestamps();

        return $this->save();
    }
}
