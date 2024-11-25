<?php

namespace Src\Main\Queue;

interface IQueueFactory
{
    public function make(string $name = null): IQueueService;
}
