<?php

namespace Src\Main\Facade\Facades;

use Src\Main\Facade\Facade;
use Src\Main\Notifications\NotificationManager;

class Notification extends Facade
{
    protected static function getAccessor(): string
    {
        return NotificationManager::class;
    }
}
