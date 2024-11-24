<?php

namespace Src\Main\Http;

interface IResponseFactory
{
    public function make(string $content = '', int $status = 200, array $headers = []): Response;
    public function noContent(int $status = 204, array $headers = []): Response;
    public function view(string $view, array $data = [], int $status = 200, array $headers = []): Response;
    public function json(array $data = [], int $status = 200, array $headers = []): Response;
}
