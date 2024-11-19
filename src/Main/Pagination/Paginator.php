<?php

namespace Src\Main\Pagination;

use Src\Main\Support\Htmlable;

class Paginator extends AbstractPaginator
{
    public function nextPageUrl(): ?string
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
        return null;
    }
    public function render(string $view = null, array $data = []): Htmlable
    {
        $view = $view ?: static::$defaultSimpleView;

        $data = array_merge($data, ['paginator' => $this]);

        return static::viewFactory()->make($view, $data);
    }
    public function hasMorePagesWhen($hasMore = true): static
    {
        $this->hasMore = $hasMore;

        return $this;
    }
    public function toArray(): array
    {
        return [
            'current_page' => $this->currentPage(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->getPath(),
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
        ];
    }
}
