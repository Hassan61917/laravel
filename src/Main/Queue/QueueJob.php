<?php

namespace Src\Main\Queue;

use Src\Main\Bus\Traits\Dispatchable;
use Src\Main\Queue\Traits\InteractsWithJobData;
use Src\Main\Queue\Traits\InteractsWithQueue;
use Src\Main\Queue\Traits\Queueable;

abstract class QueueJob implements IShouldQueue
{
    use Queueable,
        Dispatchable,
        InteractsWithQueue,
        InteractsWithJobData;

    public bool $shouldBeEncrypted = false;
}
