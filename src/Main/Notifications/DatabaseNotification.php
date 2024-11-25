<?php

namespace Src\Main\Notifications;

use App\Models\User;
use Src\Main\Database\Eloquent\EloquentBuilder;
use Src\Main\Database\Eloquent\Model;
use Src\Main\Database\Eloquent\Relations\BelongsTo;
use Src\Main\Database\Query\QueryBuilder;

class DatabaseNotification extends Model
{
    protected string $table = 'notifications';
    protected array $guarded = [];
    protected array $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
    public function notifiable(): BelongsTo
    {
        return $this->belongsTo(User::class, "notifiable_id");
    }
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }
    public function markAsUnread(): void
    {
        if ($this->read_at != null) {
            $this->forceFill(['read_at' => null])->save();
        }
    }
    public function read(): bool
    {
        return $this->read_at !== null;
    }
    public function unread(): bool
    {
        return $this->read_at === null;
    }
    public function scopeRead(EloquentBuilder $query): QueryBuilder
    {
        return $query->whereNotNull('read_at');
    }
    public function scopeUnread(EloquentBuilder $query): QueryBuilder
    {
        return $query->whereNull('read_at');
    }
}
