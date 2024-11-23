<?php

namespace Src\Main\Auth\Authorization;

use Closure;
use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Auth\Authorization\Traits\HandleAbility;
use Src\Main\Auth\Authorization\Traits\HandleAuthorization;
use Src\Main\Auth\Authorization\Traits\HandlePolicy;
use Src\Main\Auth\Authorization\Traits\HandleUser;
use Src\Main\Auth\Authorization\Traits\WithAllowsGuests;
use Src\Main\Container\IContainer;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Utils\IObserverList;
use Src\Main\Utils\ObserverList;

class Gate implements IGate
{
    use WithAllowsGuests,
        HandleUser,
        HandleAbility,
        HandlePolicy,
        HandleAuthorization;

    protected IObserverList $before;
    protected IObserverList $after;
    protected ?Response $defaultDenialResponse = null;
    public function __construct(
        protected IContainer $container,
        protected ?IAuth $user = null,
        protected array $abilities = [],
        protected array $policies = [],
    ) {
        $this->before = new ObserverList();
        $this->after = new ObserverList();
    }
    public function defaultDenialResponse(Response $response): static
    {
        $this->defaultDenialResponse = $response;

        return $this;
    }
    public function setBefore(IObserverList $before): static
    {
        $this->before = $before;

        return $this;
    }
    public function setAfter(IObserverList $after): static
    {
        $this->after = $after;

        return $this;
    }
    public function before(Closure $callback): static
    {
        $this->before->add($callback);

        return $this;
    }
    public function after(Closure $callback): static
    {
        $this->after->add($callback);

        return $this;
    }
    public function allowIf(Closure $condition, ?string $message = null, int $code = 0): Response
    {
        return $this->authorizeOnDemand($condition, $message, $code);
    }
    public function denyIf(Closure $condition, ?string $message = null, int $code = 0): Response
    {
        return $this->authorizeOnDemand($condition, $message, $code);
    }
    public function check(array $abilities, Model $model): bool
    {
        return collect($abilities)->every(
            fn($ability) => $this->inspect($ability, $model)->allowed()
        );
    }
    public function allows(string $ability, Model $model): bool
    {
        return $this->check([$ability], $model);
    }
    public function denies(string $ability, Model $model): bool
    {
        return ! $this->allows($ability, $model);
    }
    public function any(array $abilities, Model $model): bool
    {
        return collect($abilities)->contains(fn($ability) => $this->check($ability, $model));
    }
    public function none(array $abilities, Model $model): bool
    {
        return ! $this->any($abilities, $model);
    }
    public function authorize(string $ability, Model $model): Response
    {
        return $this->inspect($ability, $model)->authorize();
    }
    public function inspect(string $ability, Model $model): Response
    {
        try {

            $result = $this->raw($ability, $model);

            return $result
                ? Response::allow()
                : $this->defaultDenialResponse ?? Response::deny();
        } catch (\Exception $e) {
            return Response::deny($e->getMessage());
        }
    }
    public function raw(string $ability, Model $model): bool
    {
        $user = $this->resolveUser();

        $result = $this->callBeforeCallbacks($user, $ability, $model);

        if ($result) {
            $result = $this->callAuthCallback($user, $ability, $model);
        }

        $this->callAfterCallbacks($user, $ability, $model);

        return $result;
    }
    public function forUser(IAuth $user): static
    {
        $gate =  new static(
            $this->container,
            $user,
            $this->abilities,
            $this->policies,
        );

        $gate
            ->setBefore($this->before)
            ->setAfter($this->after);

        return $gate;
    }
    protected function callBeforeCallbacks(?IAuth $user, string $ability, Model $model): bool
    {
        foreach ($this->before->getAll() as $before) {
            if ($this->canBeCalledWithUser($user, $before)) {
                $result = $before($user, $ability, $model) ?? true;
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }
    protected function callAfterCallbacks(?IAuth $user, string $ability, Model $model): void
    {
        foreach ($this->after->getAll() as $after) {
            if ($this->canBeCalledWithUser($user, $after)) {
                $after($user, $ability, $model);
            }
        }
    }
    protected function authorizeOnDemand(Closure $condition, string $message, int $code): Response
    {
        $user = $this->resolveUser();

        $response = $this->canBeCalledWithUser($user, $condition)
            ? $condition($user)
            : new Response(false, $message, $code);

        return $response->authorize();
    }
}
