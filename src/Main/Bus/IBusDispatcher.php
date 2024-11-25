<?php

namespace Src\Main\Bus;

use Src\Main\Queue\QueueJob;

interface IBusDispatcher
{
    public function dispatch(QueueJob $job): void;
    public function dispatchSync(QueueJob $job): void;
    public function dispatchNow(QueueJob $job): void;
}
