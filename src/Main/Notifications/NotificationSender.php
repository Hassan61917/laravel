<?php

namespace Src\Main\Notifications;

use Src\Main\Bus\IBusDispatcher;
use Src\Main\Queue\IShouldQueue;

class NotificationSender
{
    public function __construct(
        protected NotificationManager $manager,
        protected IBusDispatcher $bus,
    ) {}
    public function send(Notification $notification, INotifiable ...$users): void
    {
        if ($notification instanceof IShouldQueue) {
            $this->queueNotification($notification, ...$users);
        } else {
            $this->sendNow($notification, ...$users);
        }
    }
    public function sendNow(Notification $notification, INotifiable ...$users): void
    {
        foreach ($users as $user) {
            foreach ($notification->via() as $channel) {
                $this->sendToNotifiable($channel, $user, clone $notification);
            }
        }
    }
    protected function sendToNotifiable(string $channel, INotifiable $notifiable, Notification $notification): void
    {
        $this->manager->getChannel($channel)->send($notifiable, $notification);
    }
    protected function queueNotification(Notification $notification, INotifiable ...$users): void
    {
        $queue = $notification->queue;

        $delay = $notification->delay;

        $job = new NotificationJob($notification, $users);

        $job->onQueue($queue)->delay($delay);

        $this->bus->dispatch($job);
    }
}
