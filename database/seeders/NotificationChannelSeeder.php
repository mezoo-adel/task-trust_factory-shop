<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $channels = [
            [
                'channel' => 'email',
                'name' => 'Email Notifications',
                'description' => 'Receive notifications via email',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Future channels can be added here
            // [
            //     'channel' => 'sms',
            //     'name' => 'SMS Notifications',
            //     'description' => 'Receive notifications via SMS',
            //     'is_active' => false,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
            // [
            //     'channel' => 'push',
            //     'name' => 'Push Notifications',
            //     'description' => 'Receive push notifications',
            //     'is_active' => false,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ];

        DB::table('notification_channels')->insertOrIgnore($channels);
    }
}
