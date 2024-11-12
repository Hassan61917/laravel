<?php

namespace Src\Main\Session;

use Closure;
use Illuminate\Support\Arr;
use SessionHandlerInterface;
use Src\Main\Http\Request;
use Src\Main\Session\Handlers\CookieSessionHandler;
use Src\Main\Utils\Str;

class SessionStore implements ISessionStore
{
    protected string $id;
    protected array $attributes = [];
    protected string $serialization = 'php';
    protected bool $started = false;
    public function __construct(
        protected string $name,
        protected SessionHandlerInterface $handler
    ) {
        $this->setId();
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function setId(?string $id = null): void
    {
        $this->id = $this->isValidId($id) ? $id : $this->generateSessionId();
    }
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }
    public function put(string $key, mixed $value = null): void
    {
        Arr::set($this->attributes, $key, $value);
    }
    public function push(string $key, mixed $value): void
    {
        $result = $this->get($key, []);

        $array = is_array($result) ? [...$result, $value] : [$result, $value];

        $this->put($key, $array);
    }
    public function forget(string ...$keys): void
    {
        Arr::forget($this->attributes, $keys);
    }
    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->attributes, $key, $default);
    }
    public function pull(string $key, mixed $default = null): mixed
    {
        return Arr::pull($this->attributes, $key, $default);
    }
    public function remove(string $key): mixed
    {
        return $this->pull($key);
    }
    public function all(): array
    {
        return $this->attributes;
    }
    public function only(string ...$keys): array
    {
        return Arr::only($this->attributes, $keys);
    }
    public function except(string ...$keys): array
    {
        return Arr::except($this->attributes, $keys);
    }
    public function replace(string $key, mixed $value = null): void
    {
        $this->put($key, $value);
    }
    public function has(string ...$keys): bool
    {
        foreach ($keys as $key) {
            $value = $this->get($key);
            if (is_null($value)) {
                return false;
            }
        }
        return true;
    }
    public function hasAny(string ...$keys): bool
    {
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value) {
                return true;
            }
        }
        return false;
    }
    public function exists(string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->attributes)) {
                return true;
            }
        }
        return false;
    }
    public function missing(string ...$keys): bool
    {
        return ! $this->exists(...$keys);
    }
    public function regenerateToken(): void
    {
        $this->put('_token', $this->generateSessionId());
    }
    public function token(): string
    {
        return $this->get('_token');
    }
    public function flash(string $key, mixed $value = true): void
    {
        $this->put($key, $value);

        $this->push('_flash.new', $key);

        $this->removeFromOldFlashData([$key]);
    }
    public function previousUrl(): ?string
    {
        return $this->get('_previous.url');
    }
    public function setPreviousUrl(string $url): void
    {
        $this->put('_previous.url', $url);
    }
    public function passwordConfirmed(): void
    {
        $this->put('auth.password_confirmed_at', time());
    }
    public function now(string $key, mixed $value): void
    {
        $this->put($key, $value);

        $this->push('_flash.old', $key);
    }
    public function start(): bool
    {
        $this->loadSession();

        if (! $this->has('_token')) {
            $this->regenerateToken();
        }

        return $this->started = true;
    }
    public function save(): void
    {
        $this->ageFlashData();

        $data = serialize($this->attributes);

        $this->handler->write($this->getId(), $data);

        $this->started = false;
    }
    public function ageFlashData(): void
    {
        $oldKeys = $this->get('_flash.old', []);

        $newKeys = $this->get('_flash.new', []);

        $this->forget(...$oldKeys);

        $this->put('_flash.old', $newKeys);

        $this->put('_flash.new', []);
    }
    public function getOldInput(?string $key = null, mixed $default = null): mixed
    {
        return Arr::get($this->get('_old_input', []), $key, $default);
    }
    public function hasOldInput(?string $key = null): bool
    {
        $old = $this->getOldInput($key);

        return is_null($key) ? count($old) > 0 : ! is_null($old);
    }
    public function flush(): void
    {
        $this->attributes = [];
    }
    public function reFlash(): void
    {
        $this->mergeNewFlashes($this->get('_flash.old', []));

        $this->put('_flash.old', []);
    }
    public function keep(array $keys = null): void
    {
        $this->mergeNewFlashes($keys);

        $this->removeFromOldFlashData($keys);
    }
    public function remember(string $key, Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value) {
            return $value;
        }

        $value = $callback();

        $this->put($key, $value);

        return $value;
    }
    public function increment(string $key, int $amount = 1): mixed
    {
        $value = $this->get($key, 0);

        $this->put($key,  $value + $amount);

        return $value;
    }
    public function decrement(string $key, int $amount = 1): int
    {
        return $this->increment($key, $amount * -1);
    }
    public function invalidate(): bool
    {
        $this->flush();

        return $this->migrate(true);
    }
    public function migrate(bool $destroy = false): bool
    {
        if ($destroy) {
            $this->handler->destroy($this->getId());
        }

        $this->setId();

        return true;
    }
    public function regenerate(bool $destroy = false): bool
    {
        $result = $this->migrate($destroy);

        $this->regenerateToken();

        return $result;
    }
    public function isStarted(): bool
    {
        return $this->started;
    }
    public function setRequestOnHandler(Request $request): void
    {
        if ($this->handler instanceof CookieSessionHandler) {
            $this->handler->setRequest($request);
        }
    }
    protected function isValidId(?string $id = null): bool
    {
        return is_string($id) && ctype_alnum($id) && strlen($id) === 40;
    }
    protected function loadSession(): void
    {
        $this->attributes = array_merge($this->attributes, $this->readFromHandler());
    }
    protected function readFromHandler(): array
    {
        $data = $this->handler->read($this->getId());

        if ($data) {
            $data = unserialize($this->prepareForUnserialize($data));

            if ($data && is_array($data)) {
                return $data;
            }
        }

        return [];
    }
    protected function prepareForUnserialize(string $data): string
    {
        return $data;
    }
    protected function generateSessionId(): string
    {
        return Str::random(40);
    }
    protected function removeFromOldFlashData(array $keys): void
    {
        $oldData = $this->get('_flash.old', []);

        $this->put('_flash.old', array_diff($oldData, $keys));
    }
    protected function mergeNewFlashes(array $keys): void
    {
        $values = array_unique(array_merge($this->get('_flash.new', []), $keys));

        $this->put('_flash.new', $values);
    }
}
