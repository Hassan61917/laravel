<?php

namespace Src\Symfony\Http;

class Response
{
    public static array $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        301 => 'Moved Permanently',
        302 => 'Found',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
    ];

    protected int $statusCode;
    protected string $content;
    protected string $statusText;
    protected array $sentHeaders = [];
    protected ResponseHeaderBag $headers;
    public function __construct(
        string $content,
        int $statusCode = 200,
        array $headers = [],
    ) {
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        $this->setHeaders(new ResponseHeaderBag($headers));
    }
    public static function getStatusTexts(): array
    {
        return self::$statusTexts;
    }
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    public function getContent(): string
    {
        return $this->content;
    }
    public function setStatusCode(int $code, ?string $text = null): void
    {
        if ($code < 100 || $code >= 600) {
            throw new \InvalidArgumentException('Invalid status code');
        }

        $this->setStatusText($code, $text);

        $this->statusCode = $code;
    }
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
    public function setHeaders(ResponseHeaderBag $headers): void
    {
        $this->headers = $headers;
    }
    public function getHeaders(): ResponseHeaderBag
    {
        return $this->headers;
    }
    public function send(): static
    {
        $this->sendHeaders();

        $this->sendContent();

        return $this;
    }
    protected function setStatusText(int $code, ?string $text = null): void
    {
        if (is_null($text)) {
            $text = self::$statusTexts[$code] ?? 'unknown status';
        }

        $this->statusText = $text;
    }
    protected function sendHeaders(): static
    {
        if (headers_sent()) {
            return $this;
        }

        $this->sendAllHeaders();

        $this->sendCookies();

        $this->sendStatus();

        return $this;
    }
    protected function sendAllHeaders(): void
    {
        $headers = $this->headers->headersWithoutCookies();

        foreach ($headers as $name => $values) {
            $previousValues = $this->sentHeaders[$name] ?? null;

            if ($previousValues == $values) {
                continue;
            }

            $replace = strcasecmp($name, 'Content-Type') == 0;

            if ($previousValues && array_diff($previousValues, $values)) {
                header_remove($name);

                $previousValues = null;
            }

            $newValues = is_null($previousValues) ? $values : array_diff($values, $previousValues);

            foreach ($newValues as $value) {
                $key = $name . ":" . $value;

                header($key, $replace, $this->statusCode);
            }
        }
    }
    protected function sendCookies(): void
    {
        foreach ($this->headers->getCookies() as $cookie) {
            header('Set-Cookie: ' . $cookie, false, $this->statusCode);
        }
    }
    protected function sendStatus(): void
    {
        $statusCode = $this->statusCode;

        header("HTTP/1.1 {$statusCode} {$this->statusText}", false, $this->statusCode);
    }
    protected function sendContent(): static
    {
        echo $this->content;

        return $this;
    }
}
