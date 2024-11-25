<?php

namespace Src\Main\Notifications;

interface IChannel
{
    public function send(INotifiable $notifiable, Notification $notification);
}
