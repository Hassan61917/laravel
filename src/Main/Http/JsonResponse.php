<?php

namespace Src\Main\Http;

class JsonResponse extends Response
{
    public function getData(bool $assoc = false, int $depth = 512)
    {
        return json_decode($this->content, $assoc, $depth);
    }
}
