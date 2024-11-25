<?php

namespace Src\Main\Queue\Services\Database;

use Src\Main\Support\Traits\InteractsWithTime;

class DatabaseJobRecord
{
    use InteractsWithTime;
    public function __construct(
        protected object $record
    ) {}
    public function getRecord(): object
    {
        return $this->record;
    }
    public function increment(): int
    {
        $this->record->attempts++;

        return $this->record->attempts;
    }
    public function touch(): int
    {
        return $this->record->reserved_at = $this->currentTime();
    }
    public function __get($key)
    {
        return $this->record->{$key};
    }
}
