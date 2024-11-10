<?php

namespace Src\Main\Routing\Route;

use Src\Main\Container\IContainer;
use Src\Main\Http\Request;
use Src\Main\Http\Response;
use Src\Main\Routing\Route\Actions\IAction;
use Src\Main\Routing\Route\Validators\IRouteValidator;
use Src\Main\Routing\Route\Validators\MethodValidator;
use Src\Main\Routing\Route\Validators\UriValidator;

class Route
{
    protected static array $validators = [];
    protected array $parameters = [];
    protected IContainer $container;
    protected array $data = [
        "name" => "",
        "prefix" => "",
        "middleware" => [],
    ];
    public function __construct(
        protected string $uri,
        protected string $method,
        protected IAction $action,
        array $data = []
    ) {
        $this->data = array_merge($this->data, $data);
    }
    public static function setValidators(array $validators = []): void
    {
        if (empty($validators)) {
            $validators = self::initValidators();
        }
        static::$validators = array_merge(static::$validators, $validators);
    }
    public static function addValidator(IRouteValidator $validator): void
    {
        static::$validators[] = $validator;
    }
    public static function getValidators(): array
    {
        if (empty(static::$validators)) {
            self::setValidators();
        }
        return static::$validators;
    }
    protected static function initValidators(): array
    {
        return [
            new UriValidator(),
            new MethodValidator()
        ];
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function setUri(string $uri): static
    {
        $this->uri = $uri;
        return $this;
    }
    public function getUri(): string
    {
        return $this->uri;
    }
    public function setAction(IAction $action): void
    {
        $this->action = $action;
    }
    public function getAction(): IAction
    {
        return $this->action;
    }
    public function setData(array $data): void
    {
        $this->data = $data;
    }
    public function getData(): array
    {
        return $this->data;
    }
    public function setContainer(IContainer $container): static
    {
        $this->container = $container;

        return $this;
    }
    public function getContainer(): IContainer
    {
        return $this->container;
    }
    public function name(string $name): static
    {
        $oldName = $this->getName() ?: "";

        $this->data['name'] = $oldName . $name;

        return $this;
    }
    public function getName(): string
    {
        return $this->data["name"];
    }
    public function prefix(string $prefix): static
    {
        $this->updatePrefix($prefix);

        $uri = rtrim($prefix, '/') . '/' . ltrim($this->uri, '/');

        return $this->setUri($uri == '/' ? $uri : trim($uri, '/'));
    }
    public function getPrefix(): string
    {
        return $this->data["prefix"];
    }
    public function middleware(string ...$middlewares): static
    {
        array_push($this->data["middleware"], ...$middlewares);
        return $this;
    }
    public function getMiddlewares(): array
    {
        return $this->data["middleware"];
    }
    public function isMatch(Request $request): bool
    {
        foreach (self::getValidators() as $validator) {
            if (!$validator->isMatch($this, $request)) {
                return false;
            }
        }
        return true;
    }
    public function bind(Request $request): static
    {
        $binder = new RouteParameterBinder($this);

        $this->parameters = $binder->parameters($request);

        return $this;
    }
    public function run(): Response
    {
        return $this->action->handle($this)->get();
    }
    public function getParameters(): array
    {
        return $this->parameters;
    }
    public function getParameter(string $name, string $default = null): ?string
    {
        return $this->getParameters()[$name] ?? $default;
    }
    public function hasParameters(): bool
    {
        return isset($this->parameters);
    }
    public function replaceParameter(string $name, mixed $value): static
    {
        $this->parameters[$name] = $value;

        return $this;
    }
    public function named(string ...$names): bool
    {
        $routeName = $this->getName();

        if ($routeName == "") {
            return false;
        }

        return in_array($routeName, $names);
    }
    protected function updatePrefix(string $prefix): void
    {
        $prefix = trim($prefix, '/');

        $oldPrefix = $this->data['prefix'];

        $newPrefix = $prefix . "/" . $oldPrefix;

        if ($newPrefix != "") {
            $this->data['prefix'] = $newPrefix;
        }
    }
}
