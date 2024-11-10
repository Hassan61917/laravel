<?php

namespace Src\Main\Routing\Route\Validators;

use Src\main\Http\Request;
use Src\Main\Routing\Route\Route;

class UriValidator implements IRouteValidator
{
    public function isMatch(Route $route, Request $request): bool
    {
        $uri = "/" . ltrim($route->getUri(), '/');
        $path = rtrim($request->getPathInfo(), '/') ?: '/';
        $path = $this->convertPath($uri, $path);
        return $uri == $path;
    }
    protected function convertPath(string $uri, string $path): string
    {
        $uriParts = explode('/', $uri);
        $pathParts = explode('/', $path);
        if (count($uriParts) != count($pathParts)) {
            return $path;
        }
        $result = [];
        for ($i = 0; $i < count($uriParts); $i++) {
            $uriPart = $uriParts[$i];
            $pathPart = $pathParts[$i];
            if ($uriPart == $pathPart || str_starts_with($uriPart, "{")) {
                $result[] = $uriPart;
            }
        }
        return implode('/', $result);
    }
}
