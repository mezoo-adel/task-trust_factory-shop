<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'Cosmo Shop',
            ],
            [
                'key' => 'app_description',
                'value' => 'Premium cosmetics for your natural beauty, Selected and Picked specially for you. Brought to you by Trust Factory.',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'Cosmo Shop',
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@cosmoshop.com',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }
}
