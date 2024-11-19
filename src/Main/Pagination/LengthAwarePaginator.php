<?php

namespace Src\Main\Pagination;

use Illuminate\Support\Collection;
use Src\Main\Support\Htmlable;

class LengthAwarePaginator extends AbstractPaginator
{
    protected int $lastPage;
    public function __construct(
        Collection $items,
        protected int $total,
        int $perPage,
        int $currentPage = 1,
        array $options = []
    ) {
        parent::__construct($items, $perPage, $currentPage, $options);
        $this->lastPage = max((int) ceil($total / $perPage), 1);
    }
    public function total(): int
    {
        return $this->total;
    }
    public function linkCollection()
    {
        return collect($this->elements())->flatMap(function ($item) {
            if (! is_array($item)) {
                return [['url' => null, 'label' => '...', 'active' => false]];
            }

            return collect($item)->map(function ($url, $page) {
                return [
                    'url' => $url,
                    'label' => (string) $page,
                    'active' => $this->currentPage() === $page,
                ];
            });
        })->prepend([
            'url' => $this->previousPageUrl(),
            'label' => 'Previous',
            'active' => false,
        ])->push([
            'url' => $this->nextPageUrl(),
            'label' => 'Next',
            'active' => false,
        ]);
    }
    public function hasMorePages(): bool
    {
        return $this->currentPage() < $this->lastPage();
    }
    protected function toArray(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'last_page' => $this->lastPage(),
            'last_page_url' => $this->url($this->lastPage()),
            'links' => $this->linkCollection()->toArray(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->getPath(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
            'total' => $this->total(),
        ];
    }
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
        return null;
    }
    public function lastPage()
    {
        return $this->lastPage;
    }
    public function render(string $view = null, array $data = []): Htmlable
    {
        $view = $view ?: static::$defaultView;

        $data = array_merge($data, [
            'paginator' => $this,
            'elements' => $this->elements(),
        ]);

        return static::viewFactory()->make($view, $data);
    }
    protected function elements(): array
    {
        $window = UrlWindow::make($this);

        return array_filter([
            $window['first'],
            is_array($window['slider']) ? '...' : null,
            $window['slider'],
            is_array($window['last']) ? '...' : null,
            $window['last'],
        ]);
    }
}
