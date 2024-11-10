<?php

namespace Src\Main\Routing;

use Src\Main\Http\Request;
use Src\Main\Routing\Route\Route;

interface IRouteCollection
{
    public function add(Route $route): Route;
    public function refresh(): void;
    public function match(Request $request): Route;
    public function getByMethod(string $method): array;
    public function hasNamedRoute(string $name): bool;
    public function getByName(string $name): ?Route;
    public function getAllRoutes(): array;
    public function getNamedRoutes(): array;
}
