<?php

namespace Src\Main\Http;

use InvalidArgumentException;
use Src\Main\Http\Traits\ResponseTrait;
use Src\Symfony\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    use ResponseTrait;
    public function __construct(
        string|array $content = "",
        int $statusCode = 200,
        array $headers = []
    ) {
        parent::__construct($content, $statusCode, $headers);
    }
    public function setContent(string|array $content): void
    {
        if ($this->shouldBeJson($content)) {
            $this->header("Content-Type", "application/json");

            $content = $this->morphToJson($content);

            if (!$content) {
                throw new InvalidArgumentException(json_last_error_msg());
            }
        }
        parent::setContent($content);
    }
    public function get(): Response
    {
        return $this;
    }
    protected function shouldBeJson(string|array $content): bool
    {
        return is_array($content);
    }
    protected function morphToJson(array $content): string
    {
        return json_encode($content);
    }
}
