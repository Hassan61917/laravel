<?php

namespace Src\Main\Bus;

use Src\Main\Container\IContainer;
use Src\Main\Queue\IQueueService;
use Src\Main\Queue\QueueJob;

class BusDispatcher implements IBusDispatcher
{
    public function __construct(
        protected IContainer $container,
        protected IQueueService $queue,
    ) {}
    public function dispatch(QueueJob $job): void
    {
        $this->dispatchToQueue($job);
    }
    public function dispatchSync(QueueJob $job): void
    {
        $job = $job->onConnection('sync');

        $this->dispatchToQueue($job);
    }
    public function dispatchNow(QueueJob $job): void
    {
        $this->container->call([$job, "handle"]);
    }
    protected function dispatchToQueue(QueueJob $job): void
    {
        $this->queue->push($job, $job->queue, $job->delay);
    }
}
