<?php

namespace Src\Main\Events\Listeners;

use Closure;

class ClosureListener implements IListener
{
    public function __construct(
        public Closure $callback,
    ) {}
    public function execute(object $event): void
    {
        call_user_func($this->callback, $event);
    }
}
