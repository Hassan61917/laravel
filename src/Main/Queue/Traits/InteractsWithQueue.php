<?php

namespace Src\Main\Queue\Traits;

use Src\Main\Queue\IServiceJob;

trait InteractsWithQueue
{
    public ?IServiceJob $serviceJob = null;
    public function setServiceJob(IServiceJob $serviceJob): static
    {
        $this->serviceJob = $serviceJob;

        return $this;
    }
    public function attempts(): int
    {
        return is_null($this->serviceJob) ? 1 : $this->serviceJob->attempts();
    }
    public function delete(): void
    {
        $this->serviceJob?->delete();
    }
    public function release(int $delay = 0): void
    {
        $this->serviceJob?->release($delay);
    }
}
