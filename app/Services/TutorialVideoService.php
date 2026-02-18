<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TutorialVideoService
{
    private const CHANNEL_ID_CACHE_KEY_PREFIX = 'tutorials.youtube.channel_id.';
    private const VIDEOS_CACHE_KEY_PREFIX = 'tutorials.youtube.videos.';
    private const PLAYLISTS_CACHE_KEY_PREFIX = 'tutorials.youtube.playlists.';

    /**
     * @return array<int, array<string, string>>
     */
    public function latest(int $limit = 8): array
    {
        $maxItems = max(1, (int) config('tutorials.max_items', 24));
        $safeLimit = max(1, min($limit, $maxItems));
        $cacheMinutes = max(5, (int) config('tutorials.cache_minutes', 30));
        $videosCacheKey = $this->videosCacheKey();

        if (Cache::has($videosCacheKey)) {
            $cached = Cache::get($videosCacheKey, []);
            return array_slice(is_array($cached) ? $cached : [], 0, $safeLimit);
        }

        $fresh = $this->fetchLatestFromYouTube($maxItems);
        $ttl = count($fresh) > 0 ? $cacheMinutes : 5;
        Cache::put($videosCacheKey, $fresh, now()->addMinutes($ttl));

        return array_slice($fresh, 0, $safeLimit);
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function latestPlaylists(int $limit = 6): array
    {
        $maxItems = max(1, (int) config('tutorials.max_playlists', 12));
        $safeLimit = max(1, min($limit, $maxItems));
        $cacheMinutes = max(5, (int) config('tutorials.cache_minutes', 30));
        $playlistsCacheKey = $this->playlistsCacheKey();

        if (Cache::has($playlistsCacheKey)) {
            $cached = Cache::get($playlistsCacheKey, []);
            return array_slice(is_array($cached) ? $cached : [], 0, $safeLimit);
        }

        $fresh = $this->fetchPlaylistsFromYouTube($maxItems);
        $ttl = count($fresh) > 0 ? $cacheMinutes : 5;
        Cache::put($playlistsCacheKey, $fresh, now()->addMinutes($ttl));

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

    /**
     * @return array<int, array<string, string>>
     */
    private function fetchPlaylistsFromYouTube(int $maxItems): array
    {
        $playlistsUrl = $this->playlistsUrl();
        if ($playlistsUrl === '') {
            return [];
        }

        $response = Http::accept('text/html')
            ->timeout(8)
            ->withUserAgent('ArosoftTutorialFeed/1.0')
            ->get($playlistsUrl);

        if (!$response->ok()) {
            return [];
        }

        return $this->parsePlaylists($response->body(), $maxItems);
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
        $channelCacheKey = $this->channelIdCacheKey($channelUrl);

        if (Cache::has($channelCacheKey)) {
            return (string) Cache::get($channelCacheKey, '');
        }

        $discoveredId = $this->discoverChannelId($channelUrl);
        Cache::put(
            $channelCacheKey,
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

        if (preg_match('/<meta\s+itemprop="identifier"\s+content="(UC[a-zA-Z0-9_-]{20,})"/i', $html, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/<link\s+rel="alternate"\s+type="application\/rss\+xml".*?channel_id=(UC[a-zA-Z0-9_-]{20,})/i', $html, $matches) === 1) {
            return $matches[1];
        }

        if (preg_match('/"browseId":"(UC[a-zA-Z0-9_-]{20,})"/', $html, $matches) === 1) {
            return $matches[1];
        }

        return '';
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parsePlaylists(string $html, int $maxItems): array
    {
        $playlists = $this->parsePlaylistsFromInitialData($html, $maxItems);
        if (count($playlists) > 0) {
            return $playlists;
        }

        return $this->parsePlaylistsFromHtmlAnchors($html, $maxItems);
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parsePlaylistsFromInitialData(string $html, int $maxItems): array
    {
        $json = $this->extractInitialDataJson($html);
        if ($json === '') {
            return [];
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $playlists = [];
        $this->collectPlaylists($data, $playlists, $maxItems);

        return array_values(array_slice($playlists, 0, $maxItems));
    }

    /**
     * @param array<string, mixed> $node
     * @param array<string, array<string, string>> $playlists
     */
    private function collectPlaylists(array $node, array &$playlists, int $maxItems): void
    {
        if (count($playlists) >= $maxItems) {
            return;
        }

        if (isset($node['playlistRenderer']) && is_array($node['playlistRenderer'])) {
            $this->pushPlaylist($node['playlistRenderer'], $playlists);
        }

        if (isset($node['gridPlaylistRenderer']) && is_array($node['gridPlaylistRenderer'])) {
            $this->pushPlaylist($node['gridPlaylistRenderer'], $playlists);
        }

        if (count($playlists) >= $maxItems) {
            return;
        }

        foreach ($node as $value) {
            if (count($playlists) >= $maxItems) {
                break;
            }

            if (is_array($value)) {
                $this->collectPlaylists($value, $playlists, $maxItems);
            }
        }
    }

    /**
     * @param array<string, mixed> $renderer
     * @param array<string, array<string, string>> $playlists
     */
    private function pushPlaylist(array $renderer, array &$playlists): void
    {
        $playlistId = trim((string) ($renderer['playlistId'] ?? ''));
        $endpointUrl = trim((string) data_get($renderer, 'navigationEndpoint.commandMetadata.webCommandMetadata.url', ''));

        if ($playlistId === '' && $endpointUrl !== '') {
            $playlistId = $this->playlistIdFromUrl($endpointUrl);
        }

        if ($playlistId === '' || !$this->looksLikePlaylistId($playlistId) || isset($playlists[$playlistId])) {
            return;
        }

        $title = trim($this->textFromNode($renderer['title'] ?? null));
        $videoCount = trim($this->textFromNode(
            $renderer['videoCountText']
            ?? $renderer['videoCountShortText']
            ?? $renderer['videoCount']
            ?? null
        ));

        if ($videoCount !== '' && !Str::contains(Str::lower($videoCount), 'video')) {
            $videoCount .= ' videos';
        }

        $playlists[$playlistId] = [
            'title' => Str::limit($title !== '' ? $title : 'YouTube Playlist', 120, '...'),
            'url' => $endpointUrl !== ''
                ? $this->absoluteYouTubeUrl($endpointUrl)
                : 'https://www.youtube.com/playlist?list='.$playlistId,
            'thumb' => $this->playlistThumbnail($renderer),
            'meta' => $videoCount !== '' ? $videoCount : 'Playlist',
            'playlist_id' => $playlistId,
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function parsePlaylistsFromHtmlAnchors(string $html, int $maxItems): array
    {
        $playlists = [];

        $matchCount = preg_match_all('/<a[^>]+href="\/playlist\?list=([A-Za-z0-9_-]{10,})"[^>]*>(.*?)<\/a>/si', $html, $matches, PREG_SET_ORDER);
        if (!is_int($matchCount) || $matchCount < 1) {
            return [];
        }

        foreach ($matches as $match) {
            $playlistId = trim((string) ($match[1] ?? ''));
            if ($playlistId === '' || isset($playlists[$playlistId])) {
                continue;
            }

            $title = trim(html_entity_decode(strip_tags((string) ($match[2] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($title === '' && preg_match('/title="([^"]+)"/i', (string) ($match[0] ?? ''), $titleMatch) === 1) {
                $title = trim(html_entity_decode($titleMatch[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            if ($title === '') {
                continue;
            }

            $playlists[$playlistId] = [
                'title' => Str::limit($title, 120, '...'),
                'url' => 'https://www.youtube.com/playlist?list='.$playlistId,
                'thumb' => '',
                'meta' => 'Playlist',
                'playlist_id' => $playlistId,
            ];

            if (count($playlists) >= $maxItems) {
                break;
            }
        }

        return array_values($playlists);
    }

    private function extractInitialDataJson(string $html): string
    {
        $markers = [
            'var ytInitialData = ',
            'window["ytInitialData"] = ',
            'ytInitialData = ',
        ];

        foreach ($markers as $marker) {
            $markerPos = strpos($html, $marker);
            if ($markerPos === false) {
                continue;
            }

            $jsonStart = strpos($html, '{', $markerPos);
            if ($jsonStart === false) {
                continue;
            }

            $json = $this->extractBalancedJsonObject($html, $jsonStart);
            if ($json !== '') {
                return $json;
            }
        }

        return '';
    }

    private function extractBalancedJsonObject(string $text, int $jsonStart): string
    {
        $length = strlen($text);
        $depth = 0;
        $inString = false;
        $escaped = false;

        for ($i = $jsonStart; $i < $length; $i++) {
            $char = $text[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $depth++;
                continue;
            }

            if ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr($text, $jsonStart, $i - $jsonStart + 1);
                }

                if ($depth < 0) {
                    return '';
                }
            }
        }

        return '';
    }

    /**
     * @param mixed $node
     */
    private function textFromNode(mixed $node): string
    {
        if (is_string($node)) {
            return trim($node);
        }

        if (!is_array($node)) {
            return '';
        }

        if (isset($node['simpleText']) && is_string($node['simpleText'])) {
            return trim($node['simpleText']);
        }

        if (isset($node['runs']) && is_array($node['runs'])) {
            $parts = collect($node['runs'])
                ->map(static function ($run): string {
                    if (!is_array($run)) {
                        return '';
                    }

                    return trim((string) ($run['text'] ?? ''));
                })
                ->filter(static fn (string $part): bool => $part !== '')
                ->values();

            return trim($parts->implode(''));
        }

        $accessibility = trim((string) data_get($node, 'accessibility.accessibilityData.label', ''));
        if ($accessibility !== '') {
            return $accessibility;
        }

        return '';
    }

    /**
     * @param array<string, mixed> $renderer
     */
    private function playlistThumbnail(array $renderer): string
    {
        $sets = [
            data_get($renderer, 'thumbnail.thumbnails', []),
            data_get($renderer, 'thumbnailRenderer.playlistVideoThumbnailRenderer.thumbnail.thumbnails', []),
            data_get($renderer, 'thumbnailRenderer.playlistCustomThumbnailRenderer.thumbnail.thumbnails', []),
            data_get($renderer, 'thumbnailRenderer.playlistVideoThumbnailRenderer.thumbnailRenderer.thumbnail.thumbnails', []),
        ];

        foreach ($sets as $set) {
            if (!is_array($set) || count($set) === 0) {
                continue;
            }

            $last = end($set);
            if (!is_array($last)) {
                continue;
            }

            $url = trim((string) ($last['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $url = str_replace('\u0026', '&', $url);

            if (Str::startsWith($url, '//')) {
                return 'https:'.$url;
            }

            if (Str::startsWith($url, ['http://', 'https://'])) {
                return $url;
            }
        }

        return '';
    }

    private function absoluteYouTubeUrl(string $url): string
    {
        $decoded = str_replace('\u0026', '&', trim($url));

        if (Str::startsWith($decoded, ['http://', 'https://'])) {
            return $decoded;
        }

        if (Str::startsWith($decoded, '//')) {
            return 'https:'.$decoded;
        }

        return 'https://www.youtube.com'.(Str::startsWith($decoded, '/') ? $decoded : '/'.$decoded);
    }

    private function playlistIdFromUrl(string $url): string
    {
        $decoded = str_replace('\u0026', '&', $url);
        $query = parse_url($decoded, PHP_URL_QUERY);
        if (!is_string($query) || $query === '') {
            return '';
        }

        parse_str($query, $parts);
        return trim((string) ($parts['list'] ?? ''));
    }

    private function looksLikePlaylistId(string $playlistId): bool
    {
        return preg_match('/^[A-Za-z0-9_-]{10,}$/', $playlistId) === 1;
    }

    private function playlistsUrl(): string
    {
        $channelId = trim((string) config('tutorials.youtube_channel_id', ''));
        if ($channelId !== '') {
            return 'https://www.youtube.com/channel/'.$channelId.'/playlists';
        }

        $channelUrl = trim((string) config('tutorials.youtube_channel_url', ''));
        if ($channelUrl === '') {
            return '';
        }

        $normalized = rtrim($channelUrl, '/');
        if (Str::endsWith(Str::lower($normalized), '/playlists')) {
            return $normalized;
        }

        return $normalized.'/playlists';
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

    private function channelIdCacheKey(string $channelUrl): string
    {
        return self::CHANNEL_ID_CACHE_KEY_PREFIX.sha1(Str::lower(trim($channelUrl)));
    }

    private function videosCacheKey(): string
    {
        return self::VIDEOS_CACHE_KEY_PREFIX.sha1(Str::lower($this->channelIdentity()));
    }

    private function playlistsCacheKey(): string
    {
        return self::PLAYLISTS_CACHE_KEY_PREFIX.sha1(Str::lower($this->channelIdentity()));
    }

    private function channelIdentity(): string
    {
        $configuredChannelId = trim((string) config('tutorials.youtube_channel_id', ''));
        $channelUrl = trim((string) config('tutorials.youtube_channel_url', ''));

        return $configuredChannelId !== '' ? $configuredChannelId : $channelUrl;
    }
}
