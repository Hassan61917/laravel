<?php

namespace Src\Main\Queue;

interface IJobHandler
{
    public function handle(IServiceJob $job, array $data): void;
}
