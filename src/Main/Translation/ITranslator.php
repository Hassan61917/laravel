<?php

namespace Src\Main\Translation;

interface ITranslator
{
    public function get(string $key, string $language): string;
    public function has(string $key, string $language): bool;
}
