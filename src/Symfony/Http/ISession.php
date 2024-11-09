<?php

namespace Src\Symfony\Http;

interface ISession
{
    public function start(): bool;
    public function getId(): string;
    public function setId(string $id): void;
    public function getName(): string;
    public function setName(string $name): void;
    public function invalidate(?int $lifetime = null): bool;
    public function migrate(bool $destroy = false, ?int $lifetime = null): bool;
    public function save(): void;
    public function has(string $name): bool;
    public function get(string $name, mixed $default = null): mixed;
    public function set(string $name, mixed $value): void;
    public function all(): array;
    public function replace(string $attribute): void;
    public function remove(string $name): mixed;
    public function clear(): void;
    public function isStarted(): bool;
    public function registerBag(ISessionBag $bag): void;
    public function getBag(string $name): ISessionBag;
}
