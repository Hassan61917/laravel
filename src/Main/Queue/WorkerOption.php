<?php

namespace Src\Main\Queue;

class WorkerOption
{
    public function __construct(
        public string $name = "default",
        public int $backoff = 0,
        public int $maxJob = PHP_INT_MAX,
        public int $maxTime = PHP_INT_MAX,
        public int $rest = 0,
        public int $maxTries = 1,
        public int $sleep = 1,
        public int $timeout = 60,
        public bool $force = false,
        public bool $stopWhenEmpty = false,
    ) {}
}
