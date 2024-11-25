<?php

namespace Src\Main\Queue;

use RuntimeException;
use Src\Main\Bus\IBusDispatcher;
use Src\Main\Container\IContainer;
use Src\Main\Encryption\IEncryptor;

class JobHandler implements IJobHandler
{
    public function __construct(
        protected IContainer $container,
        protected IBusDispatcher $dispatcher
    ) {}
    public function handle(IServiceJob $job, array $data): void
    {
        try {
            $queueJob = $this->getCommand($data);

            $queueJob = $this->setJobInstance($job, $queueJob);

            $this->dispatch($queueJob);

            if (! $job->isDeletedOrReleased()) {
                $job->delete();
            }
        } catch (\Exception $e) {
            $job->fail($e);
        }
    }
    protected function getCommand(array $data): QueueJob
    {
        $job = $data['command'];

        if (str_starts_with($job, 'O:')) {
            return unserialize($job);
        }

        if ($this->container->bound(IEncryptor::class)) {
            return unserialize($this->container[IEncryptor::class]->decrypt($job));
        }

        throw new RuntimeException('Unable to extract job payload.');
    }
    protected function setJobInstance(IServiceJob $job, QueueJob $instance): QueueJob
    {
        return $instance->setServiceJob($job);
    }
    protected function dispatch(QueueJob $queueJob): void
    {
        $this->dispatcher->dispatchNow($queueJob);
    }
}
