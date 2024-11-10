<?php

namespace Src\Main\Routing\Route;

use Src\Main\Http\Request;

class RouteParameterBinder
{
    public function __construct(
        protected Route $route
    ) {}
    public function parameters(Request $request): array
    {
        return $this->bindPathParameters($request);
    }
    protected function bindPathParameters(Request $request): array
    {
        $uriParts = explode("/", "/" . trim($this->route->getUri(), "/"));

        $pathParts = explode('/', $request->getPathInfo());

        $result = [];

        for ($i = 0; $i < count($uriParts); $i++) {
            $uriPart = $uriParts[$i];
            if (str_starts_with($uriPart, "{")) {
                [$name, $field] = $this->format($uriPart);
                $value = $pathParts[$i];
                $result[$name] = compact('value', 'field');
            }
        }

        return $result;
    }
    protected function format(string $part): array
    {
        $part = str_replace(["{", "}"], "", $part);

        if (str_contains($part, ":")) {
            return explode(":", $part);
        }

        return [$part, null];
    }
}
