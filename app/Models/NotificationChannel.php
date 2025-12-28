<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationChannel extends Model
{
    protected $fillable = [
        'channel',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(UserNotificationSubscription::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_notification_subscriptions')
            ->withPivot('is_subscribed')
            ->withTimestamps();
    }
}
