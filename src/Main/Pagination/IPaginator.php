<?php

namespace Src\Main\Pagination;

use Src\Main\Support\Htmlable;

interface IPaginator
{
    public function url(int $page): string;
    public function appends(array $values): static;
    public function append(string $key, string $value = null): static;
    public function nextPageUrl(): ?string;
    public function previousPageUrl(): ?string;
    public function items(): array;
    public function perPage(): int;
    public function hasPages(): bool;
    public function hasMorePages(): bool;
    public function getPath(): string;
    public function isEmpty(): bool;
    public function isNotEmpty(): bool;
    public function render(string $view = null, array $data = []): Htmlable;
}
