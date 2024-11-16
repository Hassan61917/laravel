<?php

namespace Src\Main\Http;

use Exception;
use Src\Main\Routing\Route\Route;
use Src\Main\Utils\Str;

class UrlGenerationException extends Exception
{
    public static function forMissingParameters(Route $route, array $parameters = []): static
    {
        $parameterLabel = Str::pluralStudly('parameter', count($parameters));

        $message = sprintf(
            'Missing required %s for [Route: %s] [URI: %s]',
            $parameterLabel,
            $route->getName(),
            $route->getUri()
        );

        if (count($parameters) > 0) {
            $message .= sprintf(' [Missing %s: %s]', $parameterLabel, implode(', ', $parameters));
        }

        $message .= '.';

        return new static($message);
    }
}
