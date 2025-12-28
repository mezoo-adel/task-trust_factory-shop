<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotificationSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'notification_channel_id',
        'is_subscribed',
    ];

    protected function casts(): array
    {
        return [
            'is_subscribed' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channel()
    {
        return $this->belongsTo(NotificationChannel::class, 'notification_channel_id');
    }
}
