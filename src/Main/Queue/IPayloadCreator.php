<?php

namespace Src\Main\Queue;

interface IPayloadCreator
{
    public function create(QueueJob $job, array $data = []): string;
}
