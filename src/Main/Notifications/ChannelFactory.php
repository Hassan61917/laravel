<?php

namespace Src\Main\Notifications;

use Src\Main\Notifications\Channels\DatabaseChannel;

class ChannelFactory implements IChannelFactory
{
    public function make(string $driver): IChannel
    {
        return match ($driver) {
            "database" => new DatabaseChannel(),
        };
    }
}
