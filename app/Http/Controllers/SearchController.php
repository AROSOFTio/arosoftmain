<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\TutorialVideoService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    private const MAX_RESULTS = 28;
    private const MAX_PER_SECTION = 6;

    public function __construct(
        private readonly TutorialVideoService $tutorialVideoService,
    ) {
    }

    public function suggestions(Request $request): JsonResponse
    {
        $queryText = trim((string) $request->query('q', ''));

        if ($queryText === '') {
            return response()->json(['results' => []]);
        }

        $tokens = $this->tokens($queryText);

        $results = $this->searchBlog($queryText, $tokens)
            ->concat($this->searchTutorials($queryText, $tokens))
            ->concat($this->searchStaticIndex($queryText, $tokens))
            ->sortByDesc('score')
            ->values();

        $sectionCounts = [];
        $limited = $results
            ->filter(function (array $item) use (&$sectionCounts): bool {
                $type = $item['type'];
                $sectionCounts[$type] = ($sectionCounts[$type] ?? 0) + 1;

                return $sectionCounts[$type] <= self::MAX_PER_SECTION;
            })
            ->take(self::MAX_RESULTS)
            ->map(static fn (array $item): array => Arr::only($item, ['type', 'title', 'url', 'meta']))
            ->values();

        return response()->json([
            'results' => $limited,
        ]);
    }

    /**
     * @param array<int, string> $tokens
     * @return Collection<int, array<string, mixed>>
     */
    private function searchBlog(string $queryText, array $tokens): Collection
    {
        $term = '%'.str_replace(' ', '%', $queryText).'%';

        return BlogPost::query()
            ->publiclyVisible()
            ->where(function (Builder $query) use ($term): void {
                $query->where('title', 'like', $term)
                    ->orWhere('excerpt', 'like', $term)
                    ->orWhere('body', 'like', $term);
            })
            ->orderByDesc('published_at')
            ->limit(14)
            ->get(['slug', 'title', 'excerpt', 'published_at'])
            ->map(function (BlogPost $post) use ($queryText, $tokens): array {
                $searchable = trim(implode(' ', [
                    (string) $post->title,
                    (string) $post->excerpt,
                ]));

                return [
                    'type' => 'Blog',
                    'title' => (string) $post->title,
                    'url' => route('blog.show', $post->slug),
                    'meta' => $post->published_at?->format('M j, Y') ?: 'Blog article',
                    'score' => $this->scoreMatch($searchable, (string) $post->title, $queryText, $tokens) + 30,
                ];
            })
            ->values();
    }

    /**
     * @param array<int, string> $tokens
     * @return Collection<int, array<string, mixed>>
     */
    private function searchTutorials(string $queryText, array $tokens): Collection
    {
        return collect($this->tutorialVideoService->latest(24))
            ->map(function (array $video) use ($queryText, $tokens): ?array {
                $title = (string) ($video['title'] ?? '');
                $url = (string) ($video['url'] ?? '');

                if ($title === '' || $url === '') {
                    return null;
                }

                $searchable = trim(implode(' ', [
                    $title,
                    (string) ($video['date'] ?? ''),
                ]));

                $score = $this->scoreMatch($searchable, $title, $queryText, $tokens);
                if ($score === 0) {
                    return null;
                }

                return [
                    'type' => 'Tutorials',
                    'title' => $title,
                    'url' => $url,
                    'meta' => (string) ($video['date'] ?? 'YouTube'),
                    'score' => $score + 24,
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param array<int, string> $tokens
     * @return Collection<int, array<string, mixed>>
     */
    private function searchStaticIndex(string $queryText, array $tokens): Collection
    {
        return collect($this->staticIndex())
            ->map(function (array $item) use ($queryText, $tokens): ?array {
                $searchable = trim(implode(' ', [
                    (string) ($item['title'] ?? ''),
                    (string) ($item['meta'] ?? ''),
                    (string) ($item['keywords'] ?? ''),
                ]));

                $score = $this->scoreMatch($searchable, (string) $item['title'], $queryText, $tokens);
                if ($score === 0) {
                    return null;
                }

                $item['score'] = $score;
                return $item;
            })
            ->filter()
            ->values();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function staticIndex(): array
    {
        $items = [
            [
                'type' => 'Pages',
                'title' => 'Home',
                'url' => route('home'),
                'meta' => 'Main landing page',
                'keywords' => 'arosoft homepage',
            ],
            [
                'type' => 'Pages',
                'title' => 'Blog',
                'url' => route('blog'),
                'meta' => 'Articles and updates',
                'keywords' => 'blog posts insights',
            ],
            [
                'type' => 'Pages',
                'title' => 'About',
                'url' => route('about'),
                'meta' => 'Company profile',
                'keywords' => 'about company',
            ],
            [
                'type' => 'Pages',
                'title' => 'Contact',
                'url' => route('contact'),
                'meta' => 'Reach the team',
                'keywords' => 'contact email whatsapp phone',
            ],
            [
                'type' => 'Pages',
                'title' => 'Privacy',
                'url' => route('privacy'),
                'meta' => 'Policy and compliance',
                'keywords' => 'privacy policy data',
            ],
            [
                'type' => 'Services',
                'title' => 'Services',
                'url' => route('services'),
                'meta' => 'Solutions overview',
                'keywords' => 'it services system development',
            ],
            [
                'type' => 'Services',
                'title' => 'Printing',
                'url' => route('services.printing'),
                'meta' => 'Print materials',
                'keywords' => 'printing business cards banners flyers',
            ],
            [
                'type' => 'Services',
                'title' => 'Website Design',
                'url' => route('services.website-design'),
                'meta' => 'UI and UX design',
                'keywords' => 'web design ui ux',
            ],
            [
                'type' => 'Services',
                'title' => 'Web Development',
                'url' => route('services.web-development'),
                'meta' => 'Custom development',
                'keywords' => 'laravel development engineering',
            ],
            [
                'type' => 'Courses',
                'title' => 'Training/Courses',
                'url' => route('services.training-courses'),
                'meta' => 'Technical learning',
                'keywords' => 'courses training internship classes',
            ],
            [
                'type' => 'Tutorials',
                'title' => 'Tutorials',
                'url' => route('tutorials'),
                'meta' => 'Implementation guides',
                'keywords' => 'tutorial guides walkthroughs',
            ],
            [
                'type' => 'Tools',
                'title' => 'Tools',
                'url' => route('tools'),
                'meta' => 'IT tools directory',
                'keywords' => 'converter generator remover',
            ],
        ];

        $toolCategories = config('it_tools.categories', []);

        foreach ($toolCategories as $category) {
            $categoryName = (string) ($category['name'] ?? 'Tools');

            foreach (($category['tools'] ?? []) as $tool) {
                $slug = trim((string) ($tool['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $items[] = [
                    'type' => 'Tools',
                    'title' => (string) ($tool['name'] ?? Str::headline(str_replace('-', ' ', $slug))),
                    'url' => route('tools.show', ['slug' => $slug]),
                    'meta' => $categoryName,
                    'keywords' => trim(implode(' ', [
                        (string) ($tool['tagline'] ?? ''),
                        (string) ($tool['description'] ?? ''),
                        $categoryName,
                        $slug,
                    ])),
                ];
            }
        }

        return $items;
    }

    /**
     * @param array<int, string> $tokens
     */
    private function scoreMatch(string $searchableText, string $title, string $queryText, array $tokens): int
    {
        $searchable = Str::lower($searchableText);
        $titleLower = Str::lower($title);
        $query = Str::lower($queryText);

        $score = 0;

        if (Str::contains($titleLower, $query)) {
            $score += 80;
        }

        if (Str::contains($searchable, $query)) {
            $score += 45;
        }

        $tokenHits = 0;

        foreach ($tokens as $token) {
            if (Str::contains($searchable, $token)) {
                $tokenHits++;
                $score += 8;
            }

            if (Str::contains($titleLower, $token)) {
                $score += 12;
            }
        }

        if (!$this->isFullMatch($query, $tokenHits, count($tokens))) {
            return 0;
        }

        return $score;
    }

    private function isFullMatch(string $query, int $tokenHits, int $tokenCount): bool
    {
        if ($query === '') {
            return false;
        }

        if ($tokenCount === 0) {
            return true;
        }

        return $tokenHits >= $tokenCount;
    }

    /**
     * @return array<int, string>
     */
    private function tokens(string $queryText): array
    {
        $parts = preg_split('/\s+/', Str::lower($queryText)) ?: [];

        return array_values(array_filter($parts, static fn (string $part): bool => strlen($part) >= 2));
    }
}
