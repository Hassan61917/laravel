<?php

namespace Src\Main\Notifications;

use Src\Main\Container\IContainer;
use Src\Main\Foundation\DriverManager;

class ChannelManager extends DriverManager
{
    public function __construct(
        IContainer $container,
        protected IChannelFactory $channelFactory
    ) {
        parent::__construct($container);
    }
    protected function create(string $driver): IChannel
    {
        return $this->channelFactory->make($driver);
    }
}
