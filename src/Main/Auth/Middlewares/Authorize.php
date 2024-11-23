<?php

namespace Src\Main\Auth\Middlewares;

use InvalidArgumentException;
use Src\Main\Auth\Authorization\IGate;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Http\Middleware;
use Src\Main\Http\Request;
use Src\Main\Http\Response;

class Authorize extends Middleware
{
    public function __construct(
        protected IGate $gate
    ) {}
    public static function using(string $ability, string ...$models): string
    {
        return static::class . ':' . implode(',', [$ability, ...$models]);
    }
    protected function doHandle(Request $request, string ...$args): ?Response
    {
        return $this->authorize($request, $args);
    }
    protected function authorize(Request $request, array $args): Response
    {
        [$ability, $model] = $args;

        $this->gate->authorize($ability, $this->getGateArguments($request, $model));

        return $this->handleNext($request, ...$args);
    }
    protected function getGateArguments(Request $request, ?string $model): Model
    {
        $result = $this->getModel($request, $model);

        if (is_null($result)) {
            throw new InvalidArgumentException("model Not Found Exception");
        }

        return $result;
    }
    protected function getModel(Request $request, ?string $model): ?Model
    {
        return $request->getRouteParam($model);
    }
}
