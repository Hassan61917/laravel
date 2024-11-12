<?php

namespace Src\Main\Session\Handlers;

use Src\Main\Support\Traits\InteractsWithTime;

class ArraySessionHandler implements \SessionHandlerInterface
{
    use InteractsWithTime;
    protected array $storage = [];
    public function __construct(
        protected int $minutes
    ) {}
    public function open(string $path, string $name): bool
    {
        return true;
    }
    public function close(): bool
    {
        return true;
    }
    public function read(string $id): string
    {
        if (! isset($this->storage[$id])) {
            return '';
        }

        $session = $this->storage[$id];

        $expiration = $this->calculateExpiration($this->minutes * 60);

        if (isset($session['time']) && $session['time'] >= $expiration) {
            return $session['data'];
        }

        return '';
    }
    public function write(string $id, string $data): bool
    {
        $this->storage[$id] = [
            'data' => $data,
            'time' => $this->currentTime(),
        ];

        return true;
    }
    public function destroy($id): bool
    {
        if (isset($this->storage[$id])) {
            unset($this->storage[$id]);
        }

        return true;
    }
    public function gc(int $max_lifetime): int
    {
        $expiration = $this->calculateExpiration($max_lifetime);

        $deletedSessions = 0;

        foreach ($this->storage as $sessionId => $session) {
            if ($session['time'] < $expiration) {
                unset($this->storage[$sessionId]);
                $deletedSessions++;
            }
        }

        return $deletedSessions;
    }
    protected function calculateExpiration(int $seconds): int
    {
        return $this->currentTime() - $seconds;
    }
}
