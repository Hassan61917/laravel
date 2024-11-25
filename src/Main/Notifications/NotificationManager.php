<?php

namespace Src\Main\Notifications;

use Closure;
use Src\Main\Bus\IBusDispatcher;

class NotificationManager
{
    public function __construct(
        protected ChannelManager $channelManager,
        protected IBusDispatcher $bus
    ) {}
    public function addChannel(string $name, Closure $channel): void
    {
        $this->channelManager->extend($name, $channel);
    }
    public function getChannel(string $channel): IChannel
    {
        return $this->channelManager->getDriver($channel);
    }
    public function send(Notification $notification, INotifiable ...$users): void
    {
        $sender = $this->createSender();
        $sender->send($notification, ...$users);
    }
    public function sendNow(Notification $notification, INotifiable ...$users): void
    {
        $sender = $this->createSender();
        $sender->sendNow($notification, ...$users);
    }
    protected function createSender(): NotificationSender
    {
        return new NotificationSender($this, $this->bus);
    }
}
