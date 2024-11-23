<?php

namespace Src\Main\Cache\Services\File;

use Exception;
use Src\Main\Cache\Services\ICacheStore;
use Src\Main\Filesystem\Filesystem;
use Src\Main\Support\Traits\InteractsWithTime;

class FileStore implements ICacheStore
{
    use InteractsWithTime;

    protected string $path;
    public function __construct(
        protected Filesystem $files,
        protected array $config
    ) {
        $this->path = $this->config['path'];
    }

    public function put(string $key, mixed $value, int $seconds = null): mixed
    {
        $path = $this->path($key);

        $this->createDirectory($path);

        $content = $this->expiration($seconds) . "|" . serialize($value);

        $this->files->put($path, $content);

        return $value;
    }
    public function forget(string $key): void
    {
        if ($this->files->exists($file = $this->path($key))) {
            $this->files->delete($file);
        }
    }
    public function forever(string $key, mixed $value): void
    {
        $this->put($key, $value, 0);
    }
    public function flush(): void
    {
        if (! $this->files->isDirectory($this->path)) {
            return;
        }

        foreach ($this->files->directories($this->path) as $directory) {
            $deleted = $this->files->deleteDirectory($directory);

            if (! $deleted || $this->files->exists($directory)) {
                return;
            }
        }
    }
    public function get(string $key): mixed
    {
        return $this->getPayload($key)['data'] ?? null;
    }
    public function getPrefix(): string
    {
        return '';
    }
    public function increment(int $key, int $number = 1): void
    {
        $raw = $this->getPayload($key);

        $data = $raw["data"];

        if (!is_numeric($data)) {
            return;
        }

        $this->put($key, $data + $number, $raw['time'] ?? 0);
    }
    public function decrement(string $key, int $number = 1): void
    {
        $this->increment($key, $number * -1);
    }
    protected function expiration(int $seconds): int
    {
        $time = $this->availableAt($seconds);

        return $seconds === 0 ? 9999999999 : $time;
    }
    protected function parseContent(string $content): array
    {
        [$expire, $value] = explode("|", $content);

        $value = unserialize($value);

        return [$expire, $value];
    }
    protected function path(string $key): string
    {
        $hash = md5($key);
        return "{$this->path}/{$hash}";
    }
    protected function createDirectory(string $path): void
    {
        $directory = dirname($path);

        if (! $this->files->exists($directory)) {
            $this->files->makeDirectory($directory, 0777, true, true);
        }
    }
    protected function getPayload(string $key): array
    {
        $path = $this->path($key);

        try {
            $content = $this->files->get($path);

            if (is_null($content)) {
                return $this->emptyPayload();
            }

            [$expire, $data] = $this->parseContent($content);

            if ($this->currentTime() >= $expire) {
                throw new Exception("Expired data");
            }

            $time = $expire - $this->currentTime();

            return compact('data', 'time');
        } catch (Exception) {
            $this->forget($key);
            return $this->emptyPayload();
        }
    }
    protected function emptyPayload(): array
    {
        return ['data' => null, 'time' => null];
    }
}
