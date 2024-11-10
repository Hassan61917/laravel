<?php

namespace Src\Main\Support\Traits;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateInterval;
use DateTimeInterface;

trait InteractsWithTime
{
    protected function secondsUntil(int|DateTimeInterface $delay): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
            ? max(0, $delay->getTimestamp() - $this->currentTime())
            : (int) $delay;
    }
    protected function availableAt(int|DateTimeInterface $delay = 0): int
    {
        $delay = $this->parseDateInterval($delay);

        return $delay instanceof DateTimeInterface
            ? $delay->getTimestamp()
            : Carbon::now()->addSeconds($delay)->getTimestamp();
    }
    protected function parseDateInterval(int|DateTimeInterface $delay): DateTimeInterface|int
    {
        if ($delay instanceof DateInterval) {
            $delay = Carbon::now()->add($delay);
        }

        return $delay;
    }
    protected function currentTime(): int
    {
        return Carbon::now()->getTimestamp();
    }
    protected function runTimeForHumans(float $startTime, float $endTime = null): string
    {
        $endTime ??= microtime(true);

        $runTime = ($endTime - $startTime) * 1000;

        return $runTime > 1000
            ? CarbonInterval::milliseconds($runTime)->cascade()->forHumans(short: true)
            : number_format($runTime, 2) . 'ms';
    }
}
