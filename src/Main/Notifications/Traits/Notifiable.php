<?php

namespace Src\Main\Notifications\Traits;

trait Notifiable
{
    use HasDatabaseNotifications, RoutesNotifications;
}
