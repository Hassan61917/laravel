<?php

namespace Src\Main\Notifications;

use Src\Main\Queue\Traits\Queueable;

abstract class Notification
{
    use Queueable;
    protected array $defaults = [
        "database"
    ];
    public abstract function via(): array;
    public function getMessages(INotifiable $notifiable): array
    {
        return array_merge(
            $this->buildDefaultMessages($notifiable),
            $this->buildMessages($notifiable)
        );
    }
    protected function buildMessages(INotifiable $notifiable): array
    {
        return [];
    }
    protected function buildDefaultMessages(INotifiable $notifiable): array
    {
        $result = [];

        foreach ($this->defaults as $channel) {
            if (method_exists($this, $channel)) {
                $result[$channel] = $this->$channel($notifiable);
            }
        }

        return $result;
    }
}
