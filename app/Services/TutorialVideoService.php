<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TutorialVideoService
{
    private const CHANNEL_ID_CACHE_KEY = 'tutorials.youtube.channel_id';
    private const VIDEOS_CACHE_KEY = 'tutorials.youtube.videos';

    /**
     * @return array<int, array<string, string>>
     */
    public function latest(int $limit = 8): array
    {
        $maxItems = max(1, (int) config('tutorials.max_items', 24));
        $safeLimit = max(1, min($limit, $maxItems));
        $cacheMinutes = max(5, (int) config('tutorials.cache_minutes', 30));

        if (Cache::has(self::VIDEOS_CACHE_KEY)) {
            $cached = Cache::get(self::VIDEOS_CACHE_KEY, []);
            return array_slice(is_array($cached) ? $cached : [], 0, $safeLimit);
        }

        $fresh = $this->fetchLatestFromYouTube($maxItems);
        Cache::put(self::VIDEOS_CACHE_KEY, $fresh, now()->addMinutes($cacheMinutes));

        return array_slice($fresh, 0, $safeLimit);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fetchLatestFromYouTube(int $maxItems): array
    {
        $channelId = $this->resolveChannelId();
        if ($channelId === '') {
            return [];
        }

        $feedUrl = 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId;

        $response = Http::accept('application/xml')
            ->timeout(8)
            ->withUserAgent('ArosoftTutorialFeed/1.0')
            ->get($feedUrl);

        if (!$response->ok()) {
            return [];
        }

        return $this->parseFeed($response->body(), $maxItems);
    }

    private function resolveChannelId(): string
    {
        $configuredChannelId = trim((string) config('tutorials.youtube_channel_id', ''));
        if ($configuredChannelId !== '') {
            return $configuredChannelId;
        }

        $channelUrl = trim((string) config('tutorials.youtube_channel_url', ''));
        if ($channelUrl === '') {
            return '';
        }

        $cacheMinutes = max(60, (int) config('tutorials.cache_minutes', 30) * 8);
        if (Cache::has(self::CHANNEL_ID_CACHE_KEY)) {
            return (string) Cache::get(self::CHANNEL_ID_CACHE_KEY, '');
        }

        $discoveredId = $this->discoverChannelId($channelUrl);
        Cache::put(
            self::CHANNEL_ID_CACHE_KEY,
            $discoveredId,
            now()->addMinutes($discoveredId !== '' ? $cacheMinutes : 15)
        );

        return $discoveredId;
    }

    private function discoverChannelId(string $channelUrl): string
    {
        $response = Http::timeout(8)
            ->withUserAgent('ArosoftTutorialFeed/1.0')
            ->get($channelUrl);

        if (!$response->ok()) {
            return '';
        }

        $html = $response->body();

        if (preg_match('/"channelId":"(UC[a-zA-Z0-9_-]{20,})"/', $html, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/https:\/\/www\.youtube\.com\/channel\/(UC[a-zA-Z0-9_-]{20,})/', $html, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parseFeed(string $xml, int $maxItems): array
    {
        $feed = @simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($feed === false) {
            return [];
        }

        $namespaces = $feed->getNamespaces(true);
        $ytNs = $namespaces['yt'] ?? '';
        $mediaNs = $namespaces['media'] ?? '';

        $videos = [];

        foreach ($feed->entry as $entry) {
            if (count($videos) >= $maxItems) {
                break;
            }

            $yt = $ytNs !== '' ? $entry->children($ytNs) : null;
            $media = $mediaNs !== '' ? $entry->children($mediaNs) : null;

            $videoId = trim((string) ($yt?->videoId ?? ''));
            $title = trim((string) $entry->title);
            $url = trim((string) ($entry->link['href'] ?? ''));
            $publishedRaw = trim((string) $entry->published);

            if ($title === '' || $url === '') {
                continue;
            }

            $publishedDate = '';
            if ($publishedRaw !== '') {
                try {
                    $publishedDate = Carbon::parse($publishedRaw)->format('M j, Y');
                } catch (\Throwable) {
                    $publishedDate = '';
                }
            }

            $thumb = '';
            if ($media !== null && isset($media->group->thumbnail)) {
                foreach ($media->group->thumbnail as $thumbnail) {
                    $thumbUrl = trim((string) $thumbnail->attributes()->url);
                    if ($thumbUrl !== '') {
                        $thumb = $thumbUrl;
                        break;
                    }
                }
            }

            if ($thumb === '' && $videoId !== '') {
                $thumb = 'https://i.ytimg.com/vi/'.$videoId.'/hqdefault.jpg';
            }

            $videos[] = [
                'title' => Str::limit($title, 140, '...'),
                'url' => $url,
                'thumb' => $thumb,
                'date' => $publishedDate !== '' ? $publishedDate : 'YouTube',
                'video_id' => $videoId,
            ];
        }

        return $videos;
    }
}
