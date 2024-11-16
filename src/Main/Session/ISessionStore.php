<?php

namespace Src\Main\Session;

use SessionHandlerInterface;
use Src\Main\Http\Request;

interface ISessionStore
{
    public function getName(): string;
    public function setName(string $name): void;
    public function getId(): string;
    public function setId(?string $id = null): void;
    public function start(): bool;
    public function save(): void;
    public function all(): array;
    public function exists(string ...$keys): bool;
    public function has(string ...$keys): bool;
    public function get(string $key, mixed $default = null): mixed;
    public function pull(string $key, mixed $default = null): mixed;
    public function put(string $key, mixed $value = null): void;
    public function flash(string $key, mixed $value = true): void;
    public function replace(string $key, mixed $value = null): void;
    public function token(): string;
    public function regenerateToken(): void;
    public function remove(string $key): mixed;
    public function forget(string ...$keys): void;
    public function flush(): void;
    public function invalidate(): bool;
    public function regenerate(bool $destroy = false): bool;
    public function migrate(bool $destroy = false): bool;
    public function isStarted(): bool;
    public function previousUrl(): ?string;
    public function setPreviousUrl(string $url): void;
    public function getHandler(): SessionHandlerInterface;
    public function setRequestOnHandler(Request $request): void;
}
