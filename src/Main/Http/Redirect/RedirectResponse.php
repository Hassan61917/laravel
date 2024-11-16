<?php

namespace Src\Main\Http\Redirect;

use Src\Main\Http\Request;
use Src\Main\Session\ISessionStore;
use Src\Main\Support\IMessageProvider;
use Src\Main\Support\ViewErrorBag;

class RedirectResponse extends BaseRedirectResponse
{
    protected Request $request;
    protected ISessionStore $session;
    public function setRequest(Request $request): static
    {
        $this->request = $request;

        return $this;
    }
    public function getRequest(): Request
    {
        return $this->request;
    }
    public function setSession(ISessionStore $session): static
    {
        $this->session = $session;

        return $this;
    }
    public function getSession(): ISessionStore
    {
        return $this->session;
    }
    public function with(string $key, mixed $value = null): static
    {
        $this->session->flash($key, $value);

        return $this;
    }
    public function withCookies(array $cookies): static
    {
        foreach ($cookies as $cookie) {
            $this->headers->setCookie($cookie);
        }

        return $this;
    }
    public function withInput(array $input = []): static
    {
        $input = count($input) > 0 ? $input : $this->request->input();

        $this->session->flash(
            "_old_input",
            $this->removeFilesFromInput($input)
        );

        return $this;
    }
    public function onlyInput(string ...$keys): static
    {
        return $this->withInput($this->request->only(...$keys));
    }
    public function exceptInput(string ...$keys): static
    {
        return $this->withInput($this->request->except(...$keys));
    }
    public function withErrors(IMessageProvider $provider, string $key = 'default'): static
    {
        $errors = $this->session->get('errors', new ViewErrorBag());

        $value = $provider->getMessageBag();

        $this->session->flash(
            'errors',
            $errors->put($key, $value)
        );

        return $this;
    }
    protected function removeFilesFromInput(array $input): array
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = $this->removeFilesFromInput($value);
            }
        }

        return $input;
    }
}
