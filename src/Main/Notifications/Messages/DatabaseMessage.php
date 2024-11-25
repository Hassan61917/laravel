<?php

namespace Src\Main\Notifications\Messages;

class DatabaseMessage extends ChannelMessage
{
    public function __construct(
        public array $data = []
    ) {}
}
