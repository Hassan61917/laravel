<?php

namespace Src\Symfony\Http;

class RequestBuilder
{
    protected IRequestInput $request;
    protected IRequestInput $query;
    protected IRequestInput $server;
    protected IRequestInput $headers;
    protected IRequestInput $cookies;

    public function setQuery(array $query): static
    {
        $this->query = new RequestInput($query);
        return $this;
    }
    public function getQuery(): IRequestInput
    {
        return $this->query;
    }
    public function setRequest(array $request): static
    {
        $this->request = new RequestInput($request);
        return $this;
    }
    public function getRequest(): IRequestInput
    {
        return $this->request;
    }
    public function setHeaderFromServer(): static
    {
        $this->setHeaders($this->createHeaders());
        return $this;
    }
    public function setHeaders(array $headers): static
    {
        $this->headers = new RequestInput($headers);
        return $this;
    }
    public function getHeaders(): IRequestInput
    {
        return $this->headers;
    }
    public function setCookies(array $cookies): static
    {
        $this->cookies = new RequestInput($cookies);
        return $this;
    }
    public function getCookies(): IRequestInput
    {
        return $this->cookies;
    }
    public function setServer(array $server): static
    {
        $this->server = new RequestInput($server);
        return $this;
    }
    public function getServer(): IRequestInput
    {
        return $this->server;
    }
    protected function createHeaders(): array
    {
        $serverHeaders = $this->server->all();
        $headers = [];
        foreach ($serverHeaders as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH']) && $value !== '') {
                $headers[$key] = $value;
            }
        }
        $authorizationHeader = $serverHeaders['HTTP_AUTHORIZATION'] ?? "";
        if (str_contains($authorizationHeader, 'bearer ')) {
            $headers['HTTP_AUTHORIZATION'] = $authorizationHeader;
        }
        return $headers;
    }
}
