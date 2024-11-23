<?php

namespace Src\Main\Session\Handlers;

use Carbon\Carbon;
use SessionHandlerInterface;
use Src\Main\Auth\Authentication\IGuard;
use Src\Main\Container\IContainer;
use Src\Main\Database\Connections\Connection;
use Src\Main\Database\Exceptions\QueryException;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Support\Traits\InteractsWithTime;
class DatabaseSessionHandler implements SessionHandlerInterface
{
    use InteractsWithTime;
    protected bool $exists = false;
    public function __construct(
        protected Connection $connection,
        protected string $table,
        protected int $minutes,
        protected ?IContainer $container,
    ) {}
    public function close(): bool
    {
        return true;
    }
    public function open(string $path, string $name): bool
    {
        return true;
    }
    public function write(string $id, string $data): bool
    {
        $payload = $this->getDefaultPayload($data);

        if (! $this->exists) {
            $this->read($id);
        }

        if ($this->exists) {
            $this->performUpdate($id, $payload);
        } else {
            $this->performInsert($id, $payload);
        }

        return $this->exists = true;
    }
    public function read(string $id): string|false
    {
        $session = $this->getQuery()->find($id);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->payload)) {
            $this->exists = true;

            return base64_decode($session->payload);
        }

        return '';
    }
    public function destroy(string $id): bool
    {
        $this->getQuery()->where('id', $id)->delete();

        return true;
    }
    public function gc(int $max_lifetime): int
    {
        return $this->getQuery()->where('last_activity', '<=', $this->currentTime() - $max_lifetime)->delete();
    }
    protected function getDefaultPayload(string $data): array
    {
        $payload = [
            'payload' => base64_encode($data),
            'last_activity' => $this->currentTime(),
        ];

        if (! $this->container) {
            return $payload;
        }

        $this->addUserInformation($payload)
            ->addRequestInformation($payload);

        return $payload;
    }
    protected function addUserInformation(array &$payload): static
    {
        if ($this->container->bound(IGuard::class)) {
            $payload['user_id'] = $this->userId();
        }

        return $this;
    }
    protected function userId(): string
    {
        return $this->container->make(IGuard::class)->id();
    }
    protected function addRequestInformation(array &$payload): static
    {
        $request = $this->container->make("request");

        $payload = array_merge($payload, [
            'ip_address' => $request->getIp(),
            'user_agent' => substr($request->header('USER_AGENT'), 0, 500),
        ]);

        return $this;
    }
    protected function getQuery(): QueryBuilder
    {
        return $this->connection->table($this->table);
    }
    protected function expired(?object $session): bool
    {
        if (is_null($session)) {
            return false;
        }
        return isset($session->last_activity) &&
            $session->last_activity < Carbon::now()->subMinutes($this->minutes)->getTimestamp();
    }
    protected function performInsert(string $sessionId, array $payload): bool
    {
        try {
            $payload = ["id" => $sessionId, ...$payload];
            return $this->getQuery()->insert($payload);
        } catch (QueryException) {
            $this->performUpdate($sessionId, $payload);
        }
        return false;
    }
    protected function performUpdate(string $sessionId, array $payload): bool
    {
        return $this->getQuery()->where('id', $sessionId)->update($payload);
    }
}
