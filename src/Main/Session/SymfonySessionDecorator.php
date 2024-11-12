<?php

namespace Src\Main\Session;

use BadMethodCallException;
use Src\Symfony\Http\ISession;
use Src\Symfony\Http\ISessionBag;
use Src\Main\Session\ISessionStore as IAppSession;

class SymfonySessionDecorator implements ISession
{
    public function __construct(
        protected IAppSession $store
    ) {}
    public function start(): bool
    {
        return $this->store->start();
    }
    public function getId(): string
    {
        return $this->store->getId();
    }
    public function setId(string $id): void
    {
        $this->store->setId($id);
    }
    public function getName(): string
    {
        return $this->store->getName();
    }
    public function setName(string $name): void
    {
        $this->store->setName($name);
    }
    public function invalidate(?int $lifetime = null): bool
    {
        $this->store->invalidate();

        return true;
    }
    public function migrate(bool $destroy = false, ?int $lifetime = null): bool
    {
        $this->store->migrate($destroy);

        return true;
    }
    public function save(): void
    {
        $this->store->save();
    }
    public function has(string $name): bool
    {
        return $this->store->has($name);
    }
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->store->get($name, $default);
    }
    public function set(string $name, mixed $value): void
    {
        $this->store->put($name, $value);
    }
    public function all(): array
    {
        return $this->store->all();
    }
    public function replace(string $attribute): void
    {
        $this->store->replace($attribute);
    }
    public function remove(string $name): mixed
    {
        return $this->store->remove($name);
    }
    public function clear(): void
    {
        $this->store->flush();
    }
    public function isStarted(): bool
    {
        return $this->store->isStarted();
    }
    public function registerBag(ISessionBag $bag): void
    {
        throw new BadMethodCallException('Method not implemented by Laravel.');
    }
    public function getBag(string $name): ISessionBag
    {
        throw new BadMethodCallException('Method not implemented by Laravel.');
    }
}
