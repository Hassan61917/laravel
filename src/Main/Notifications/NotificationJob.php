<?php

namespace Src\Main\Notifications;

use Src\Main\Queue\QueueJob;

class NotificationJob extends QueueJob
{
    public function __construct(
        protected Notification $notification,
        protected array $users,
    ) {}
    public function handle(NotificationManager $manager): void
    {
        $manager->sendNow($this->notification, ...$this->users);
    }
}
