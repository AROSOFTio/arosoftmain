<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

class BlogDashboardController extends Controller
{
    public function __invoke(): View
    {
        $metrics = [
            'total_posts' => BlogPost::query()->count(),
            'published_posts' => BlogPost::query()
                ->whereIn('status', ['published', 'scheduled'])
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->count(),
            'draft_posts' => BlogPost::query()->where('status', 'draft')->count(),
            'scheduled_posts' => BlogPost::query()
                ->whereIn('status', ['published', 'scheduled'])
                ->whereNotNull('published_at')
                ->where('published_at', '>', now())
                ->count(),
            'total_views' => (int) BlogPost::query()->sum('view_count'),
        ];

        $recentPosts = BlogPost::query()
            ->with(['author', 'category'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.blog.dashboard', [
            'metrics' => $metrics,
            'recentPosts' => $recentPosts,
        ]);
    }
}
