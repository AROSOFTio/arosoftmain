<?php

namespace App\Services;

use DOMComment;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use DOMXPath;

class BlogContentSanitizer
{
    /**
     * @var list<string>
     */
    private array $allowedTags = [
        'p',
        'a',
        'ul',
        'ol',
        'li',
        'strong',
        'em',
        'b',
        'i',
        'h2',
        'h3',
        'h4',
        'img',
        'blockquote',
        'pre',
        'code',
        'table',
        'thead',
        'tbody',
        'tr',
        'th',
        'td',
        'iframe',
        'figure',
        'figcaption',
        'br',
        'hr',
    ];

    /**
     * @var array<string, list<string>>
     */
    private array $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'title'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'figure' => [],
        'figcaption' => [],
    ];

    public function sanitizeForStorage(string $html): string
    {
        $html = trim($html);

        if ($html === '') {
            return '';
        }

        $dom = $this->createDocument($html);
        $root = $this->rootNode($dom);

        if (!$root instanceof DOMElement) {
            return '';
        }

        $this->replaceVideoLinkParagraphs($dom, $root);
        $this->sanitizeChildren($dom, $root);

        return trim($this->innerHtml($root));
    }

    private function createDocument(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="blog-content-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        return $dom;
    }

    private function rootNode(DOMDocument $dom): ?DOMNode
    {
        $xpath = new DOMXPath($dom);

        return $xpath->query("//*[@id='blog-content-root']")?->item(0);
    }

    private function replaceVideoLinkParagraphs(DOMDocument $dom, DOMElement $root): void
    {
        $xpath = new DOMXPath($dom);
        $paragraphs = $xpath->query('.//p', $root);

        if (!$paragraphs) {
            return;
        }

        foreach (iterator_to_array($paragraphs) as $paragraph) {
            if (!$paragraph instanceof DOMElement) {
                continue;
            }

            $url = null;

            if ($paragraph->childNodes->length === 1) {
                $first = $paragraph->firstChild;

                if ($first instanceof DOMText) {
                    $url = trim($first->wholeText);
                }

                if ($first instanceof DOMElement && strtolower($first->tagName) === 'a') {
                    $url = trim($first->getAttribute('href'));
                }
            }

            if (!$url) {
                continue;
            }

            $embedUrl = $this->normalizeEmbedUrl($url);

            if (!$embedUrl) {
                continue;
            }

            $figure = $dom->createElement('figure');
            $iframe = $dom->createElement('iframe');
            $iframe->setAttribute('src', $embedUrl);
            $iframe->setAttribute('title', 'Embedded video player');
            $iframe->setAttribute('loading', 'lazy');
            $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
            $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
            $figure->appendChild($iframe);

            $paragraph->parentNode?->replaceChild($figure, $paragraph);
        }
    }

    private function sanitizeChildren(DOMDocument $dom, DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof DOMComment) {
                $parent->removeChild($node);
                continue;
            }

            if ($node instanceof DOMText) {
                continue;
            }

            if (!$node instanceof DOMElement) {
                $parent->removeChild($node);
                continue;
            }

            $tag = strtolower($node->tagName);

            if (!in_array($tag, $this->allowedTags, true)) {
                if (in_array($tag, ['script', 'style', 'object', 'embed', 'svg', 'math'], true)) {
                    $parent->removeChild($node);
                    continue;
                }

                $this->sanitizeChildren($dom, $node);
                $this->unwrapNode($node);
                continue;
            }

            $this->sanitizeAttributes($node, $tag);

            if ($tag === 'iframe' && !$this->sanitizeIframe($node)) {
                $parent->removeChild($node);
                continue;
            }

            if ($tag === 'a') {
                $this->sanitizeAnchor($node);
            }

            if ($tag === 'img') {
                $this->sanitizeImage($node);
            }

            $this->sanitizeChildren($dom, $node);
        }
    }

    private function unwrapNode(DOMElement $node): void
    {
        $parent = $node->parentNode;

        if (!$parent) {
            return;
        }

        while ($node->firstChild) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $allowed = $this->allowedAttributes[$tag] ?? [];

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->name);

            if (str_starts_with($name, 'on') || $name === 'style') {
                $element->removeAttribute($attribute->name);
                continue;
            }

            if (!in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->name);
            }
        }
    }

    private function sanitizeAnchor(DOMElement $anchor): void
    {
        $href = trim($anchor->getAttribute('href'));
        $safeHref = $this->sanitizeUrl($href, ['http', 'https', 'mailto', 'tel']);

        if (!$safeHref) {
            $anchor->removeAttribute('href');
        } else {
            $anchor->setAttribute('href', $safeHref);
        }

        $target = strtolower(trim($anchor->getAttribute('target')));
        if ($target !== '_blank') {
            $anchor->removeAttribute('target');
            $anchor->removeAttribute('rel');
            return;
        }

        $anchor->setAttribute('target', '_blank');
        $anchor->setAttribute('rel', 'noopener noreferrer nofollow');
    }

    private function sanitizeImage(DOMElement $image): void
    {
        $src = trim($image->getAttribute('src'));
        $safeSrc = $this->sanitizeUrl($src, ['http', 'https']);

        if (!$safeSrc) {
            $image->parentNode?->removeChild($image);
            return;
        }

        $image->setAttribute('src', $safeSrc);

        if (!$image->getAttribute('loading')) {
            $image->setAttribute('loading', 'lazy');
        }
    }

    private function sanitizeIframe(DOMElement $iframe): bool
    {
        $src = trim($iframe->getAttribute('src'));
        $embedUrl = $this->normalizeEmbedUrl($src);

        if (!$embedUrl) {
            return false;
        }

        $iframe->setAttribute('src', $embedUrl);
        $iframe->setAttribute('loading', 'lazy');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');

        if (!$iframe->getAttribute('allow')) {
            $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        }

        return true;
    }

    /**
     * @param list<string> $allowedSchemes
     */
    private function sanitizeUrl(string $url, array $allowedSchemes): ?string
    {
        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if (!in_array($scheme, $allowedSchemes, true)) {
            return null;
        }

        return $url;
    }

    private function normalizeEmbedUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = (string) parse_url($url, PHP_URL_QUERY);

        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            if (str_starts_with($path, '/embed/')) {
                $id = trim(substr($path, 7));
                return $this->youtubeEmbedFromId($id);
            }

            parse_str($query, $params);
            if (!empty($params['v'])) {
                return $this->youtubeEmbedFromId((string) $params['v']);
            }
        }

        if ($host === 'youtu.be') {
            $id = trim($path, '/');
            return $this->youtubeEmbedFromId($id);
        }

        if (in_array($host, ['vimeo.com', 'www.vimeo.com'], true)) {
            $id = trim($path, '/');
            return $this->vimeoEmbedFromId($id);
        }

        if ($host === 'player.vimeo.com' && str_starts_with($path, '/video/')) {
            $id = trim(substr($path, 7), '/');
            return $this->vimeoEmbedFromId($id);
        }

        return null;
    }

    private function youtubeEmbedFromId(string $id): ?string
    {
        if (!preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id)) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$id;
    }

    private function vimeoEmbedFromId(string $id): ?string
    {
        if (!preg_match('/^\d{4,20}$/', $id)) {
            return null;
        }

        return 'https://player.vimeo.com/video/'.$id;
    }

    private function innerHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $childNode) {
            $html .= $node->ownerDocument?->saveHTML($childNode) ?? '';
        }

        return $html;
    }
}
