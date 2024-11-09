<?php

namespace Src\Symfony\Http;

class Request
{
    public const  METHOD_GET = 'GET';
    public const  METHOD_POST = 'POST';
    public const  METHOD_PUT = 'PUT';
    public const  METHOD_DELETE = 'DELETE';
    protected static bool $httpMethodOverride = false;
    protected string $requestUri;
    protected string $pathInfo;
    protected string $method;
    protected $content = null;
    protected IRequestInput $query;
    protected IRequestInput $request;
    protected IRequestInput $headers;
    protected IRequestInput $cookies;
    protected IRequestInput $server;

    protected ?ISession $session = null;
    protected array $acceptableContentTypes = [];
    public function __construct(
        protected RequestBuilder $builder
    ) {
        $this->init($this->builder);
    }
    public static function createFromGlobals(): static
    {
        $builder = new RequestBuilder();

        $builder
            ->setQuery($_GET)
            ->setRequest($_POST)
            ->setCookies($_COOKIE)
            ->setServer($_SERVER)
            ->setHeaderFromServer();

        return self::createFrom($builder);
    }
    public static function enableHttpMethodOverride(): void
    {
        self::$httpMethodOverride = true;
    }
    public static function disableHttpMethodOverride(): void
    {
        self::$httpMethodOverride = false;
    }
    public static function getHttpMethodOverride(): bool
    {
        return self::$httpMethodOverride;
    }
    protected static function createFrom(RequestBuilder $builder): static
    {
        return new static($builder);
    }
    public static function normalizeQueryString(?string $qs): ?string
    {
        if ($qs) {

            $result = HeaderUtils::parseQuery($qs);

            return http_build_query($result, '', '&', \PHP_QUERY_RFC3986);
        }
        return null;
    }
    public function all(): array
    {
        return array_merge(
            $this->query->all(),
            $this->request->all()
        );
    }
    public function get(string $key, string $default = null): string
    {
        if ($this->query->has($key)) {
            return $this->query->get($key);
        }

        if ($this->request->has($key)) {
            return $this->request->get($key);
        }

        return $default;
    }
    public function getQueryString(): ?string
    {
        return static::normalizeQueryString($this->server->get('QUERY_STRING'));
    }
    public function getHeaders(): array
    {
        return $this->headers->all();
    }
    public function getCookies(): IRequestInput
    {
        return $this->cookies;
    }
    public function getIp(): string
    {
        return $this->server->get('REMOTE_ADDR');
    }
    public function getUserAgent(): string
    {
        return $this->headers->get('User-Agent');
    }
    public function getScriptName(): string
    {
        return $this->server->get('SCRIPT_NAME', "");
    }
    public function getRequestUri(): string
    {
        return $this->requestUri ??= $this->prepareRequestUri();
    }
    public function getPathInfo(): string
    {
        return $this->pathInfo ??= $this->preparePathInfo();
    }
    public function getMethod(): string
    {
        return $this->method ?? $this->prepareMethod();
    }
    public function isXmlHttpRequest(): bool
    {
        return $this->headers->get('X-Requested-With') == "XMLHttpRequest";
    }
    public function getUri(): string
    {
        $qs = $this->getQueryString();

        if ($qs) {
            $qs = '?' . $qs;
        }

        return $this->getSchemeAndHttpHost() . $this->getPathInfo() . $qs;
    }
    public function getSchemeAndHttpHost(): string
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }
    public function getScheme(): string
    {
        return $this->isSecure() ? 'https' : 'http';
    }
    public function getHttpHost(): string
    {
        $port = $this->getPort();

        if ($port == 80 || $port == 443) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }
    public function getPort(): int
    {
        $host = $this->headers->get('HOST');

        $pos = strrpos($host, ':');

        if ($pos) {
            return (int) substr($host, $pos + 1);
        }

        return $this->getScheme() == "https" ? 443 : 80;
    }
    public function getHost(): string
    {
        $host = $this->headers->get('HOST');

        return strtolower(preg_replace('/:\d+$/', '', trim($host)));
    }
    public function isSecure(): bool
    {
        $https = $this->server->get('HTTPS');

        return !empty($https) && strtolower($https) != "off";
    }
    public function getRealMethod(): string
    {
        return strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }
    public function getContent(bool $asResource = false): string
    {
        $currentContentIsResource = is_resource($this->content);

        if ($asResource) {
            if ($currentContentIsResource) {
                rewind($this->content);

                return $this->content;
            }

            if (is_string($this->content)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);

                return $resource;
            }

            $this->content = false;

            return fopen('php://input', 'r');
        }

        if ($currentContentIsResource) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if (is_null($this->content) || false === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }
    public function hasSession(): bool
    {
        return  $this->session != null;
    }
    public function setSession(ISession $session): void
    {
        $this->session = $session;
    }
    public function getAcceptableContentTypes(): array
    {
        return $this->acceptableContentTypes ??= array_map(
            'strval',
            array_keys(
                AcceptHeader::fromString($this->headers->get('Accept'))->all()
            )
        );
    }
    protected function init(RequestBuilder $builder): void
    {
        $this->request = $builder->getRequest();
        $this->query = $builder->getQuery();
        $this->server = $builder->getServer();
        $this->headers = $builder->getHeaders();
        $this->cookies = $builder->getCookies();
    }
    protected function prepareRequestUri(): string
    {
        $requestUri = $this->server->get('REQUEST_URI', "");

        if ($requestUri && $requestUri[0] === "/") {
            if ($pos = strpos($requestUri, '#')) {
                $requestUri = substr($requestUri, 0, $pos);
            }
        }

        $this->server->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }
    protected function preparePathInfo(): string
    {
        $requestUri = $this->getRequestUri();

        if ($requestUri == "") {
            return '/';
        }

        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        if ($requestUri != "" && $requestUri[0] != "/") {
            $requestUri = '/' . $requestUri;
        }

        return $requestUri;
    }
    protected function prepareMethod(): string
    {
        $method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

        if (
            $method == self::METHOD_POST
            && self::$httpMethodOverride
            && in_array($method, ['PUT', 'DELETE'])
        ) {
            $method = strtoupper($this->request->get('_method', "POST"));
        }

        return $method;
    }
    public function __get(string $key): ?string
    {
        return $this->all()[$key] ?? null;
    }
}
