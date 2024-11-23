<?php

namespace Src\Main\Auth;

use Closure;
use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Hashing\Drivers\IHashDriver;

class EloquentUserProvider implements IUserProvider
{
    protected Closure $queryCallback;
    public function __construct(
        protected IHashDriver $hashDriver,
        protected string      $model
    ) {}
    public function getHashDriver(): IHashDriver
    {
        return $this->hashDriver;
    }
    public function getModel(): string
    {
        return $this->model;
    }
    public function createModel(): ?IAuth
    {
        try {
            return new $this->model;
        } catch (\Exception) {
            return null;
        }
    }
    public function withQuery(Closure $queryCallback = null): static
    {
        $this->queryCallback = $queryCallback;

        return $this;
    }
    public function getQueryCallback(): ?Closure
    {
        return $this->queryCallback;
    }
    public function getById(string $id): ?IAuth
    {
        $model = $this->createModel();

        return $this->newModelQuery($model)
            ->where($model->getIdName(), $id)
            ->first();
    }
    public function getByToken(string $id, string $token): ?IAuth
    {
        $model = $this->createModel();

        $user = $this->newModelQuery($model)->where(
            $model->getIdName(),
            $id
        )->first();

        if (!$user) {
            return null;
        }

        $rememberToken = $user->getRememberToken();

        return $rememberToken && hash_equals($rememberToken, $token) ? $user : null;
    }
    public function getByCredentials(array $credentials): ?IAuth
    {
        $credentials = collect($credentials)->except('password')->toArray();

        if (empty($credentials)) {
            return null;
        }

        $query = $this->newModelQuery();

        foreach ($credentials as $key => $value) {
            if (is_array($value)) {
                $query->whereIn($key, $value);
            } else {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }
    public function validateCredentials(IAuth $user, array $credentials): bool
    {
        $plain = $credentials['password'];

        if (is_null($plain)) {
            return false;
        }

        return $this->hashDriver->check($plain, $user->getAuthPassword());
    }
    public function rehashPasswordIfRequired(IAuth $user, array $credentials, bool $force = false): void
    {
        if (!$this->hashDriver->needsRehash($user->getAuthPassword()) && !$force) {
            return;
        }

        $user->forceFill([
            $user->getAuthPasswordName() => $this->hashDriver->make($credentials['password']),
        ])->save();
    }
    public function updateRememberToken(IAuth $user, string $token): void
    {
        $user->setRememberToken($token);

        $user->save();
    }
    protected function newModelQuery(?Model $model = null): EloquentBuilder
    {
        $query = is_null($model)
            ? $this->createModel()->newQuery()
            : $model->newQuery();

        if (isset($this->queryCallback)) {
            call_user_func($this->queryCallback, $query);
        }

        return $query;
    }
}
