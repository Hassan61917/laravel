<?php

namespace Src\Main\Queue\Exceptions;

use RuntimeException;
use Src\Main\Queue\IServiceJob;

class MaxAttemptsExceededException extends RuntimeException
{
    public function __construct(
        public IServiceJob $job
    ) {
        parent::__construct($this->createMessage());
    }
    protected function createMessage(): string
    {
        $jobName = $this->job->resolveName();

        return "$jobName has been attempted too many times.";
    }
}
