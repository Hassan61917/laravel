<?php

namespace Src\Main\Events\Listeners;

interface IListener
{
    public function execute(object $event): void;
}
