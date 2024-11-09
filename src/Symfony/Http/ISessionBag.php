<?php

namespace Src\Symfony\Http;

interface ISessionBag
{
    public function getName(): string;
    public function initialize(array &$array): void;
    public function getStorageKey(): string;
    public function clear(): mixed;
}
