<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get a setting value by key with caching
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            return Setting::get($key, $default);
        });
    }

    /**
     * Set a setting value and clear cache
     *
     * @param string $key
     * @param mixed $value
     * @return Setting
     */
    public function set(string $key, $value): Setting
    {
        $setting = Setting::set($key, $value);
        Cache::forget("setting.{$key}");
        return $setting;
    }

    /**
     * Get multiple settings by keys
     *
     * @param array $keys
     * @return array
     */
    public function getMany(array $keys): array
    {
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->get($key);
        }
        return $settings;
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function all(): array
    {
        return Cache::remember('settings.all', 3600, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });
    }

    /**
     * Update multiple settings at once
     *
     * @param array $settings
     * @return void
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
        Cache::forget('settings.all');
    }

    /**
     * Clear all settings cache
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::flush();
    }

    /**
     * Get system info settings (name, description, icon)
     *
     * @return array
     */
    public function getSystemInfo(): array
    {
        return [
            'name' => $this->get('app_name'),
            'description' => $this->get('app_description'),
            'icon_url' => $this->get('app_icon_url'),
        ];
    }
}
