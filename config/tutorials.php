<?php

return [
    /*
    |--------------------------------------------------------------------------
    | YouTube Channel Source
    |--------------------------------------------------------------------------
    |
    | Set your channel handle URL and, optionally, channel ID. If channel ID is
    | not provided, it will be discovered from the handle page and cached.
    |
    */
    'youtube_channel_url' => env('YOUTUBE_CHANNEL_URL', 'https://www.youtube.com/@bentech_ds'),
    'youtube_channel_id' => env('YOUTUBE_CHANNEL_ID', 'UC3c6uQ078JTwZjmeD8bC09Q'),

    /*
    |--------------------------------------------------------------------------
    | Fetch and Cache
    |--------------------------------------------------------------------------
    */
    'cache_minutes' => (int) env('YOUTUBE_VIDEOS_CACHE_MINUTES', 30),
    'max_items' => (int) env('YOUTUBE_VIDEOS_MAX_ITEMS', 24),
];
