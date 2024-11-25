<?php

namespace Src\Main\Notifications\Traits;

use App\Models\User;
use Src\Main\Facade\Facades\Notification as NotificationFacade;
use Src\Main\Notifications\Notification;

trait RoutesNotifications
{
    public function notify(Notification $notification): void
    {
        NotificationFacade::send($notification, $this);
    }
    public function notifyNow(Notification $notification): void
    {
        NotificationFacade::sendNow($notification, $this);
    }
    public function notificationFor(): User
    {
        return $this;
    }
}
