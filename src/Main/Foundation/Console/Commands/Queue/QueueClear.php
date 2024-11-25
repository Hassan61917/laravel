<?php

namespace Src\Main\Foundation\Console\Commands\Queue;

use Src\Main\Console\AppCommand;
use Src\Main\Queue\IQueueService;
use Src\Symfony\Console\Inputs\Item\InputMode;
use Src\Symfony\Console\Inputs\Item\InputOption;

class QueueClear extends AppCommand
{
    protected string $description = "Delete all of the jobs from the specified queue";

    public function __construct(
        protected IQueueService $queue
    ) {
        parent::__construct();
    }
    public function handle(): int
    {
        $connection = $this->laravel["config"]["queue.default"];

        $queueName = $this->getQueue($connection);

        if ($this->queue->clear($queueName)) {
            $this->output->write("All Jobs From {$connection}({$queueName}) got cleared");
        }

        return 0;
    }
    protected function getQueue(string $connection): string
    {
        return $this->getOption('queue') ?: $this->laravel['config']->get(
            "queue.connections.{$connection}.queue",
            'default'
        );
    }
    protected function getOptions(): array
    {
        return [
            new InputOption("queue", null, 'The name of the queue to clear', InputMode::Optional)
        ];
    }
}
