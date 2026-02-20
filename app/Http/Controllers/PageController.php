<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\TutorialVideoService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(TutorialVideoService $tutorialVideoService): View
    {
        $latestPosts = BlogPost::query()
            ->publiclyVisible()
            ->with(['author:id,name', 'category:id,name,slug'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('pages.home', [
            'latestPosts' => $latestPosts,
            'tutorialVideos' => $tutorialVideoService->latest(4),
            'tutorialPlaylists' => $tutorialVideoService->latestPlaylists(3),
            'systems' => $this->homeSystems(),
            'toolHighlights' => $this->homeToolHighlights(6),
        ]);
    }

    public function blog(): View
    {
        return $this->placeholder(
            'Blog',
            'Blog page placeholder',
            'Long-form articles and product updates will be published here next.'
        );
    }

    public function services(): View
    {
        return $this->placeholder(
            'Services',
            'Services landing',
            'This landing page will introduce all Arosoft service lines and case studies.'
        );
    }

    public function printing(): View
    {
        return $this->placeholder(
            'Printing',
            'Printing services',
            'Detailed printing packages, turnaround windows, and portfolio highlights will appear here.'
        );
    }

    public function websiteDesign(): View
    {
        return $this->placeholder(
            'Website Design',
            'Website design services',
            'Design process, UX strategy, and sample website showcases are coming soon.'
        );
    }

    public function webDevelopment(): View
    {
        return $this->placeholder(
            'Web Development',
            'Web development services',
            'Engineering capabilities, frameworks, and delivery process details will be listed here.'
        );
    }

    public function trainingCourses(): View
    {
        return $this->placeholder(
            'Training/Courses',
            'Training and courses',
            'Upcoming technical training tracks and enrollment details will be available on this page.'
        );
    }

    public function tutorials(TutorialVideoService $tutorialVideoService): View
    {
        return view('pages.tutorials', [
            'tutorialVideos' => $tutorialVideoService->latest(24),
            'tutorialPlaylists' => $tutorialVideoService->latestPlaylists(12),
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function homeSystems(): array
    {
        return [
            [
                'name' => 'Arosoft ERP',
                'label' => 'ERP',
                'summary' => 'Customized ERPNext environment for finance, stock, purchasing, and operations visibility.',
                'modules' => ['Finance', 'Inventory', 'Procurement'],
                'url' => 'https://erp.arosoft.io',
                'cta' => 'Open ERP Portal',
                'status' => 'Live',
                'external' => true,
            ],
            [
                'name' => 'Retail POS',
                'label' => 'POS',
                'summary' => 'Fast point-of-sale workflows with cashier controls, receipts, and stock movement tracking.',
                'modules' => ['Billing', 'Stock sync', 'Daily sales'],
                'url' => route('contact'),
                'cta' => 'Request POS Demo',
                'status' => 'Ready',
                'external' => false,
            ],
            [
                'name' => 'Sales CRM',
                'label' => 'CRM',
                'summary' => 'Pipeline, lead assignment, reminders, and follow-up tracking for consistent sales execution.',
                'modules' => ['Leads', 'Pipeline', 'Follow-up'],
                'url' => route('contact'),
                'cta' => 'Request CRM Demo',
                'status' => 'Ready',
                'external' => false,
            ],
            [
                'name' => 'School Management',
                'label' => 'Education',
                'summary' => 'Student records, fee tracking, timetable workflows, and parent communication modules.',
                'modules' => ['Admissions', 'Fees', 'Reports'],
                'url' => route('contact'),
                'cta' => 'Request School Demo',
                'status' => 'Ready',
                'external' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function homeToolHighlights(int $limit = 6): array
    {
        $highlights = [];
        $categories = config('it_tools.categories', []);

        foreach ($categories as $category) {
            if (!is_array($category)) {
                continue;
            }

            $categoryName = (string) ($category['name'] ?? 'Tool');
            $tools = $category['tools'] ?? [];

            if (!is_array($tools)) {
                continue;
            }

            foreach ($tools as $tool) {
                if (!is_array($tool)) {
                    continue;
                }

                $slug = trim((string) ($tool['slug'] ?? ''));
                if ($slug === '') {
                    continue;
                }

                $highlights[] = [
                    'name' => (string) ($tool['name'] ?? 'Utility Tool'),
                    'slug' => $slug,
                    'tagline' => (string) ($tool['tagline'] ?? ($tool['description'] ?? 'Practical utility tool for delivery teams.')),
                    'category' => $categoryName,
                    'status' => (string) ($tool['status'] ?? 'Live'),
                ];

                if (count($highlights) >= $limit) {
                    return $highlights;
                }
            }
        }

        return $highlights;
    }

    private function placeholder(string $title, string $heading, string $copy): View
    {
        return view('pages.placeholder', [
            'title' => $title,
            'heading' => $heading,
            'copy' => $copy,
        ]);
    }
}
