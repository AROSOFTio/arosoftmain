<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AdminSettings
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $defaults = [
            'admin_emails' => config('blog.admin_emails', []),
            'sidebar_cache_ttl_minutes' => (int) config('blog.sidebar_cache_ttl_minutes', 20),
            'feed_cache_ttl_minutes' => (int) config('blog.feed_cache_ttl_minutes', 30),
            'view_count_window_hours' => (int) config('blog.view_count_window_hours', 12),
            'dashboard_theme_default' => 'light',
        ];

        $stored = Cache::get('admin.settings', []);

        if (!is_array($stored)) {
            return $defaults;
        }

        return array_merge($defaults, $stored);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = static::all();

        return $all[$key] ?? $default;
    }

    /**
     * @param array<string, mixed> $settings
     */
    public static function put(array $settings): void
    {
        Cache::forever('admin.settings', $settings);
    }
}

