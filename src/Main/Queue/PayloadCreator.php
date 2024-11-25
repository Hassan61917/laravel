<?php

namespace Src\Main\Queue;

use InvalidArgumentException;
use Src\Main\Encryption\IEncryptor;
use Src\Main\Utils\Str;

class PayloadCreator implements IPayloadCreator
{
    public function __construct(
        protected IEncryptor $encryptor
    ) {}
    public function create(QueueJob $job, array $data = []): string
    {
        return $this->createPayload($job, $data);
    }
    protected function createPayload(QueueJob $job, array $data): string
    {
        $payload = json_encode($this->createPayloadArray($job, $data));

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Unable to JSON encode payload');
        }

        return $payload;
    }
    protected function createPayloadArray(QueueJob $job, array $data): array
    {
        $result = [
            'uuid' => Str::uuid(),
            'displayName' => get_class($job),
            "max_tries" => $job->maxTries,
            'data' => ['commandName' => get_class($job), 'command' => $this->serializeCommand($job)],
        ];

        return array_merge($result, $data);
    }
    protected function serializeCommand(QueueJob $job): string
    {
        if ($job->shouldBeEncrypted) {
            return $this->encryptor->encrypt($job);
        }

        return serialize(clone $job);
    }
}
