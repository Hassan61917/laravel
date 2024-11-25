<?php

namespace Src\Main\Notifications\Traits;

use Src\Main\Database\Eloquent\Relations\HasMany;
use Src\Main\Database\Query\QueryBuilder;
use Src\Main\Notifications\DatabaseNotification;

trait HasDatabaseNotifications
{
    public function notifications(): HasMany
    {
        return $this->hasMany(DatabaseNotification::class, 'notifiable_id')->latest();
    }
    public function readNotifications(): QueryBuilder
    {
        return $this->notifications()->read();
    }
    public function unreadNotifications(): QueryBuilder
    {
        return $this->notifications()->unread();
    }
}
