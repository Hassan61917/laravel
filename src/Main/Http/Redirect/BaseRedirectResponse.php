<?php

namespace Src\Main\Http\Redirect;

use InvalidArgumentException;
use Src\Main\Http\Response;

class BaseRedirectResponse extends Response
{
    protected string $targetUrl;
    public function __construct(
        string $url,
        int $statusCode = 302,
        array $headers = []
    ) {
        parent::__construct("", $statusCode, $headers);
        $this->setTargetUrl($url);
    }
    public function getTargetUrl(): string
    {
        return $this->targetUrl;
    }
    public function setTargetUrl(string $url): static
    {
        if (empty($url)) {
            throw new InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->targetUrl = $url;

        $this->setContent(
            sprintf('<!DOCTYPE html>
<html lang="">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="refresh" content="0;url=\'%1$s\'" />

        <title>Redirecting to %1$s</title>
    </head>
    <body>
        Redirecting to <a href="%1$s">%1$s</a>.
    </body>
</html>', htmlspecialchars($url, \ENT_QUOTES, 'UTF-8'))
        );

        $this->headers->set('Location', $url);

        return $this;
    }
}
