<?php

namespace Src\Main\Notifications\Channels;

use Src\Main\Notifications\IChannel;
use Src\Main\Notifications\INotifiable;
use Src\Main\Notifications\Messages\DatabaseMessage;
use Src\Main\Notifications\Notification;

class DatabaseChannel implements IChannel
{
    public function send(INotifiable $notifiable, Notification $notification): void
    {
        $data = $this->buildPayload($notifiable, $notification);

        $notifiable->notificationFor()->notifications()->create($data);
    }
    protected function buildPayload(INotifiable $notifiable, Notification $notification): array
    {
        return [
            'type' =>  get_class($notification),
            'data' => $this->getData($notifiable, $notification)->data,
            'read_at' => null,
        ];
    }
    protected function getData(INotifiable $notifiable, Notification $notification): DatabaseMessage
    {
        return $notification->getMessages($notifiable)["database"];
    }
}
