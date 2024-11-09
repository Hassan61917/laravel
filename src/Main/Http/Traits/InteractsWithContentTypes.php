<?php

namespace Src\Main\Http\Traits;

use Src\Main\Utils\Str;

trait InteractsWithContentTypes
{
    public function expectsJson(): bool
    {
        return ($this->ajax() && $this->acceptsAnyContentType()) || $this->wantsJson();
    }
    public function wantsJson(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && Str::contains(strtolower($acceptable[0]), '/json', '+json');
    }
    public function accepts(string ...$types): bool
    {
        $accepts = $this->getAcceptableContentTypes();

        if (count($accepts) === 0) {
            return true;
        }

        foreach ($accepts as $accept) {
            if ($accept === '*/*' || $accept === '*') {
                return true;
            }

            foreach ($types as $type) {
                $accept = strtolower($accept);

                $type = strtolower($type);

                if ($this->matchesType($accept, $type) || $accept === strtok($type, '/') . '/*') {
                    return true;
                }
            }
        }

        return false;
    }
    public function prefers(string ...$types): ?string
    {
        $accepts = $this->getAcceptableContentTypes();
        foreach ($accepts as $accept) {
            if (in_array($accept, ['*/*', '*'])) {
                return $types[0];
            }

            foreach ($types as $contentType) {
                $type = $contentType;

                $accept = strtolower($accept);

                $type = strtolower($type);

                if ($this->matchesType($type, $accept) || $accept === strtok($type, '/') . '/*') {
                    return $contentType;
                }
            }
        }
        return null;
    }
    public function acceptsAnyContentType(): bool
    {
        $acceptable = $this->getAcceptableContentTypes();

        return count($acceptable) === 0 ||
            (isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*'));
    }
    public function acceptsJson(): bool
    {
        return $this->accepts('application/json');
    }
    public function acceptsHtml(): bool
    {
        return $this->accepts('text/html');
    }
    public static function matchesType(string $actual, string $type): bool
    {
        if ($actual === $type) {
            return true;
        }

        $split = explode('/', $actual);

        return isset($split[1]) && preg_match('#' . preg_quote($split[0], '#') . '/.+\+' . preg_quote($split[1], '#') . '#', $type);
    }
}
