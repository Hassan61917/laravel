<?php

namespace Src\Main\Notifications;

use App\Models\User;

interface INotifiable
{
    public function notify(Notification $notification): void;
    public function notifyNow(Notification $notification): void;
    public function notificationFor(): User;
}
