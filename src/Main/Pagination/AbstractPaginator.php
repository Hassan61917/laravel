<?php

namespace Src\Main\Pagination;

use Closure;
use Illuminate\Support\Collection;
use Src\Main\Support\Htmlable;
use Src\Main\View\ViewManager;

abstract class AbstractPaginator extends BasePaginator
{
    protected static Closure $currentPathResolver;
    protected static Closure $currentPageResolver;
    protected static Closure $queryStringResolver;
    protected static Closure $viewFactoryResolver;
    public static string $defaultView = 'default';
    public static string $defaultSimpleView = 'simple-default';
    protected string $name = "page";
    protected int $currentPage = 1;
    protected int $onEachSide = 3;
    public function __construct(
        Collection $items,
        int $perPage = 10,
        int $currentPage = 1,
        array $options = []
    ) {
        parent::__construct($items, $perPage, $options);
        $this->setCurrentPage($currentPage);
    }
    public static function currentPathResolver(Closure $resolver): void
    {
        static::$currentPathResolver = $resolver;
    }
    public static function resolveCurrentPath(string $default = '/'): string
    {
        if (isset(static::$currentPathResolver)) {
            return call_user_func(static::$currentPathResolver);
        }

        return $default;
    }
    public static function currentPageResolver(Closure $resolver): void
    {
        static::$currentPageResolver = $resolver;
    }
    public static function resolveCurrentPage(string $pageName = 'page', int $default = 1): int
    {
        if (isset(static::$currentPageResolver)) {
            return (int) call_user_func(static::$currentPageResolver, $pageName);
        }

        return $default;
    }
    public static function queryStringResolver(Closure $resolver): void
    {
        static::$queryStringResolver = $resolver;
    }
    public static function resolveQueryString($default = null): string
    {
        if (isset(static::$queryStringResolver)) {
            return (static::$queryStringResolver)();
        }

        return $default;
    }
    public static function viewFactoryResolver(Closure $resolver): void
    {
        static::$viewFactoryResolver = $resolver;
    }
    public static function viewFactory(): ViewManager
    {
        return call_user_func(static::$viewFactoryResolver);
    }
    public static function defaultView(string $view): void
    {
        static::$defaultView = $view;
    }
    public static function defaultSimpleView($view): void
    {
        static::$defaultSimpleView = $view;
    }
    public function setPath(string $path): static
    {
        $this->path = $this->path !== '/' ? rtrim($this->path, '/') : $this->path;

        return $this;
    }
    public function setCurrentPage(int $currentPage = 1): static
    {
        $this->currentPage = $currentPage ?: static::resolveCurrentPage();
        return $this;
    }
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
    public function withQueryString(): static
    {
        if (isset(static::$queryStringResolver)) {
            return $this->appends(call_user_func(static::$queryStringResolver));
        }

        return $this;
    }
    public function previousPageUrl(): ?string
    {
        if ($this->getCurrentPage() > 1) {
            return $this->url($this->currentPage() - 1);
        }
        return null;
    }
    public function getUrlRange(int $start, int $end): array
    {
        $range = range($start, $end);
        $result = [];
        foreach ($range as $page) {
            $result[$page] = $this->url($page);
        }
        return $result;
    }
    public function firstItem(): ?int
    {
        return $this->isEmpty() ? null
            : ($this->currentPage - 1) * $this->perPage + 1;
    }
    public function lastItem(): ?int
    {
        return $this->isEmpty() ? null
            : $this->firstItem() + $this->count() - 1;
    }
    public function getOnEachSide(): int
    {
        return $this->onEachSide;
    }
    public function hasPages(): bool
    {
        return $this->currentPage() > 1 || $this->hasMorePages();
    }
    public function onFirstPage(): bool
    {
        return $this->currentPage() <= 1;
    }
    public function onLastPage(): bool
    {
        return ! $this->hasMorePages();
    }
    public function currentPage(): int
    {
        return $this->currentPage;
    }
    public function onEachSide(int $count): static
    {
        $this->onEachSide = $count;

        return $this;
    }
    public function links(string $view = null, array $data = []): Htmlable
    {
        return $this->render($view, $data);
    }
    protected function createUrlParameters(int $page): array
    {
        $page = max($page, 1);

        return [$this->name => $page];
    }
}
