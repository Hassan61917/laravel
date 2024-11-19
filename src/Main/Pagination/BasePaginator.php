<?php

namespace Src\Main\Pagination;

use Illuminate\Support\Collection;
use Src\Main\Support\Htmlable;
use Src\Main\Support\Traits\ForwardsCalls;
use Traversable;

abstract class BasePaginator implements IPaginator, Htmlable, \Stringable, \ArrayAccess, \IteratorAggregate
{
    use ForwardsCalls;
    protected string $name = "";
    protected string $path = "/";
    protected bool $hasMore = false;
    protected array $query = [];
    protected array $options = [];
    protected Collection $items;
    public function __construct(
        Collection $items,
        protected int $perPage = 10,
        array $options = []
    ) {
        $this->setItems($items);
        $this->setOptions($options);
    }
    public function setItems(Collection $items): void
    {
        $this->hasMore = $items->count() >= $this->perPage;

        $this->items = $items->slice(0, $this->perPage);
    }
    public function setOptions(array $options): void
    {
        $this->options = $options;

        foreach ($options as $key => $value) {
            $this->{$key} = $value;
        }
    }
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }
    public function getPath(): string
    {
        return $this->path;
    }
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function setCollection(Collection $collection): static
    {
        $this->items = $collection;

        return $this;
    }
    public function getCollection(): Collection
    {
        return $this->items;
    }
    public function perPage(): int
    {
        return $this->perPage;
    }
    public function hasMorePages(): bool
    {
        return $this->hasMore;
    }
    public function items(): array
    {
        return $this->items->all();
    }
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }
    public function isNotEmpty(): bool
    {
        return $this->items->isNotEmpty();
    }
    public function count(): int
    {
        return $this->items->count();
    }
    public function append(string $key, string $value = null): static
    {
        return $this->addQuery($key, $value);
    }
    public function appends(array $values): static
    {
        return $this->appendArray($values);
    }
    public function url(int $page): string
    {
        $parameters = $this->createUrlParameters($page);

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        $path = $this->getPath();

        return $path
            . (str_contains($path, '?') ? '&' : '?')
            . $this->buildQuery($parameters);
    }
    public function getIterator(): Traversable
    {
        return $this->items->getIterator();
    }
    public function offsetExists(mixed $offset): bool
    {
        return $this->items->has($offset);
    }
    public function offsetGet(mixed $offset): mixed
    {
        return $this->items->get($offset);
    }
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->items->put($offset, $value);
    }
    public function offsetUnset(mixed $offset): void
    {
        $this->items->forget($offset);
    }
    public function toHtml(): string
    {
        return $this->render()->toHtml();
    }
    public function __toString(): string
    {
        return $this->toHtml();
    }
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
    public function toJson(): string
    {
        return json_encode($this->jsonSerialize());
    }
    protected function addQuery(string $key, string $value): static
    {
        if ($key !== $this->name) {
            $this->query[$key] = $value;
        }

        return $this;
    }
    protected function appendArray(array $keys): static
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }
        return $this;
    }
    protected function buildQuery(array $parameters): string
    {
        return http_build_query($parameters, '', '&');
    }
    protected abstract function toArray(): array;
    protected abstract function createUrlParameters(int $page): array;
}
