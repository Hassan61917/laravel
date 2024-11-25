<?php

namespace Src\Main\Foundation\Console\Commands\Notification;

use Src\Main\Foundation\Console\Commands\AbstractMakeGenerator;

class MakeNotification extends AbstractMakeGenerator
{
    protected string $path = "Notifications";
    protected string $type = "Notification";
    protected string $stubsPath = "Notification";
    protected string $description = 'Create a new notification class';
    protected function getDefaultStub(): string
    {
        return "notification.stub";
    }
}
