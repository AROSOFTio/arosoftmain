<?php

return [
    'admin_emails' => array_filter(array_map(
        'trim',
        explode(',', (string) env('BLOG_ADMIN_EMAILS', ''))
    )),
    'sidebar_cache_ttl_minutes' => (int) env('BLOG_SIDEBAR_CACHE_TTL', 20),
    'feed_cache_ttl_minutes' => (int) env('BLOG_FEED_CACHE_TTL', 30),
    'view_count_window_hours' => (int) env('BLOG_VIEW_WINDOW_HOURS', 12),
];

