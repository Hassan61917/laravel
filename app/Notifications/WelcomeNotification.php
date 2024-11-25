<?php

namespace App\Notifications;

use App\Models\User;
use Src\Main\Notifications\Messages\DatabaseMessage;
use Src\Main\Notifications\Notification;
use Src\Main\Queue\IShouldQueue;

class WelcomeNotification extends Notification implements IShouldQueue
{
    public function __construct(
        public User $user
    ) {}
    public function via(): array
    {
        return ["database"];
    }
    protected function database(): DatabaseMessage
    {
        $data = [
            "message" => "Welcome {$this->user->name}"
        ];
        return new DatabaseMessage($data);
    }
}
