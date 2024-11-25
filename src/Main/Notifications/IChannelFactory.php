<?php

namespace Src\Main\Notifications;

interface IChannelFactory
{
    public function make(string $driver): IChannel;
}
