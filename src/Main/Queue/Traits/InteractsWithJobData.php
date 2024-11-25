<?php

namespace Src\Main\Queue\Traits;

trait InteractsWithJobData
{
    public int $maxTries = 0;
    public function tries(int $tries): static
    {
        $this->maxTries = $tries;

        return $this;
    }
}
