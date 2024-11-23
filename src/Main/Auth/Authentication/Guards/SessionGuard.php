<?php

namespace Src\Main\Auth\Authentication\Guards;

use InvalidArgumentException;
use RuntimeException;
use Src\Main\Auth\Authentication\IAuth;
use Src\Main\Auth\Authentication\IStatefulGuard;
use Src\Main\Auth\IUserProvider;
use Src\Main\Auth\Recaller;
use Src\Main\Auth\Traits\GuardHelper;
use Src\Main\Cookie\IQueueCookieFactory;
use Src\Main\Facade\Facades\Hash;
use Src\Main\Http\Request;
use Src\Main\Session\ISessionStore;
use Src\Main\Utils\Str;
use Src\Symfony\Http\Cookie;

class SessionGuard implements IStatefulGuard
{
    use GuardHelper;
    protected bool $viaRemember = false;
    protected bool $loggedOut = false;
    protected bool $recallAttempted = false;
    protected int $rememberDuration = 576000;
    protected ?IAuth $lastAttempted;
    public function __construct(
        protected readonly string $name,
        protected IUserProvider $provider,
        protected ISessionStore $session,
        protected IQueueCookieFactory $cookie,
        protected ?Request $request = null,
        protected bool $rehashOnLogin = true
    ) {}
    public function getName(): string
    {
        return 'login_' . $this->name . '_' . sha1(static::class);
    }
    public function getRecallerName(): string
    {
        return 'remember_' . $this->name . '_' . sha1(static::class);
    }
    public function getCookieJar(): IQueueCookieFactory
    {
        if (! isset($this->cookie)) {
            throw new RuntimeException('Cookie jar has not been set.');
        }

        return $this->cookie;
    }
    public function setRememberDuration(int $minutes): static
    {
        $this->rememberDuration = $minutes;

        return $this;
    }
    public function getRememberDuration(): int
    {
        return $this->rememberDuration;
    }
    public function getLastAttempted(): ?IAuth
    {
        return $this->lastAttempted;
    }
    public function getSession(): ISessionStore
    {
        return $this->session;
    }
    public function getUser(): ?IAuth
    {
        return $this->user;
    }
    public function getRequest(): Request
    {
        return $this->request;
    }
    public function viaRemember(): bool
    {
        return $this->viaRemember;
    }
    public function login(IAuth $user, bool $remember = false): void
    {
        $this->updateSession($user->getId());

        if ($remember) {
            $this->ensureRememberTokenIsSet($user);

            $this->queueRecallerCookie($user);
        }

        $this->setUser($user);
    }
    public function loginUsingId(string $id, bool $remember = false): ?IAuth
    {
        $user = $this->provider->getById($id);

        if ($user) {
            $this->login($user, $remember);

            return $user;
        }

        return null;
    }
    public function onceUsingId(string $id): ?IAuth
    {
        $user = $this->provider->getById($id);

        if ($user) {
            $this->setUser($user);

            return $user;
        }

        return null;
    }
    public function setUser(IAuth $user)
    {
        $this->user = $user;

        $this->loggedOut = false;

        return $this;
    }
    public function user(): ?IAuth
    {
        if ($this->loggedOut) {
            return null;
        }

        if ($this->user) {
            return $this->user;
        }

        if (($user = $this->getUserFromSession()) || ($user = $this->getUserFromCookie())) {
            $this->user = $user;
        }

        return $this->user;
    }
    public function id(): ?string
    {
        if ($this->loggedOut) {
            return null;
        }

        $user = $this->user();

        return $user
            ? $user->getId()
            : $this->session->get($this->getName());
    }
    public function validate(array $credentials = []): bool
    {
        $user = $this->provider->getByCredentials($credentials);

        $this->lastAttempted = $user;

        return $this->hasValidCredentials($user, $credentials);
    }
    public function attempt(array $credentials = [], bool $remember = false): bool
    {
        $user = $this->provider->getByCredentials($credentials);

        if ($user) {
            $this->lastAttempted = $user;
        }

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->rehashPasswordIfRequired($user, $credentials);

            $this->login($user, $remember);

            return true;
        }

        return false;
    }
    public function once(array $credentials = []): bool
    {
        if ($this->validate($credentials)) {

            $this->rehashPasswordIfRequired($this->lastAttempted, $credentials);

            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }
    public function logout(): void
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        if ($this->user && $user->getRememberToken()) {
            $this->cycleRememberToken($user);
        }

        $this->user = null;

        $this->loggedOut = true;
    }
    public function logoutCurrentDevice(): void
    {
        $user = $this->user();

        $this->clearUserDataFromStorage();

        $this->user = null;

        $this->loggedOut = true;
    }
    public function logoutOtherDevices(string $password): ?IAuth
    {
        $user = $this->user();

        if (!$user) {
            return null;
        }

        $this->rehashUserPasswordForDeviceLogout($password);

        if (
            $this->recaller() ||
            $this->getCookieJar()->hasQueued($this->getRecallerName())
        ) {
            $this->queueRecallerCookie($user);
        }

        return $user;
    }
    protected function updateSession(string $id): void
    {
        $this->session->put($this->getName(), $id);

        $this->session->migrate(true);
    }
    protected function ensureRememberTokenIsSet(IAuth $user): void
    {
        if (empty($user->getRememberToken())) {
            $this->cycleRememberToken($user);
        }
    }
    protected function cycleRememberToken(IAuth $user): void
    {
        $token = Str::random(60);

        $user->setRememberToken($token);

        $this->provider->updateRememberToken($user, $token);
    }
    protected function queueRecallerCookie(IAuth $user): void
    {
        $cookie = $this->createRecaller(
            "{$user->getId()}|{$user->getRememberToken()}|{$user->getAuthPassword()}"
        );

        $this->getCookieJar()->queue($cookie);
    }
    protected function createRecaller(string $value): Cookie
    {
        return $this->getCookieJar()->make(
            $this->getRecallerName(),
            $value,
            $this->getRememberDuration()
        );
    }
    protected function getUserFromSession(): ?IAuth
    {
        $id = $this->session->get($this->getName());

        return $id ? $this->provider->getById($id) : null;
    }
    protected function getUserFromCookie(): ?IAuth
    {
        $recaller = $this->recaller();

        if ($recaller) {
            $user = $this->userFromRecaller($recaller);

            if ($user) {
                $this->updateSession($user->getId());
            }

            return $user;
        }

        return null;
    }
    protected function recaller(): ?Recaller
    {
        if (is_null($this->request)) {
            return null;
        }

        $recaller = $this->request->getCookies()->get($this->getRecallerName());

        if ($recaller) {
            return new Recaller($recaller);
        }

        return null;
    }
    protected function userFromRecaller(Recaller $recaller): ?IAuth
    {
        if (! $recaller->valid() || $this->recallAttempted) {
            return null;
        }
        $this->recallAttempted = true;

        $user = $this->provider->getByToken($recaller->id(), $recaller->token());

        $this->viaRemember = $user != null;

        return $user;
    }
    protected function hasValidCredentials(?IAuth $user, array $credentials): bool
    {
        return $user && $this->provider->validateCredentials($user, $credentials);
    }
    protected function rehashPasswordIfRequired(IAuth $user, array $credentials): void
    {
        if ($this->rehashOnLogin) {
            $this->provider->rehashPasswordIfRequired($user, $credentials);
        }
    }
    protected function clearUserDataFromStorage(): void
    {
        $this->session->remove($this->getName());

        $cookieJar = $this->getCookieJar();

        $cookieJar->enqueue($this->getRecallerName());

        if ($this->recaller()) {
            $cookieJar->queue($cookieJar->forget($this->getRecallerName()));
        }
    }
    protected function rehashUserPasswordForDeviceLogout(string $password): void
    {
        $user = $this->user();

        if (! Hash::check($password, $user->getAuthPassword())) {
            throw new InvalidArgumentException('The given password does not match the current password.');
        }

        $this->provider->rehashPasswordIfRequired(
            $user,
            ['password' => $password],
            force: true
        );
    }
}
