<?php

namespace Src\Main\Events\Listeners;

use Src\Main\Container\IContainer;
use Src\Main\Utils\Str;

class ClassListener implements IListener
{
    public function __construct(
        protected IContainer $container,
        protected string $listener,
    ) {}
    public function execute(object $event): void
    {
        [$class, $method] = Str::parseCallback($this->listener, 'handle');

        $instance = $this->container->make($class);

        $this->container->call([$instance, $method], [$event]);
    }
}
