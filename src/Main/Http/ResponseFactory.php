<?php

namespace Src\Main\Http;

use Src\Main\View\ViewManager;

class ResponseFactory implements IResponseFactory
{
    public function __construct(
        protected ViewManager $viewManager,
    ) {}
    public function make(string $content = '', int $status = 200, array $headers = []): Response
    {
        return new Response($content, $status, $headers);
    }
    public function noContent(int $status = 204, array $headers = []): Response
    {
        return $this->make("", $status, $headers);
    }
    public function view(string $view, array $data = [], int $status = 200, array $headers = []): Response
    {
        $content = $this->viewManager->make($view, $data);

        return $this->make($content->render(), $status, $headers);
    }
    public function json(array $data = [], int $status = 200, array $headers = []): Response
    {
        return new Response(json_encode($data), $status, $headers);
    }
}
