<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminSettingsRequest;
use App\Support\AdminSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => AdminSettings::all(),
        ]);
    }

    public function update(UpdateAdminSettingsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $emails = collect(preg_split('/[,;\n]+/', (string) ($validated['admin_emails'] ?? '')) ?: [])
            ->map(fn (string $email): string => strtolower(trim($email)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        AdminSettings::put([
            'admin_emails' => $emails,
            'sidebar_cache_ttl_minutes' => (int) $validated['sidebar_cache_ttl_minutes'],
            'feed_cache_ttl_minutes' => (int) $validated['feed_cache_ttl_minutes'],
            'view_count_window_hours' => (int) $validated['view_count_window_hours'],
            'dashboard_theme_default' => $validated['dashboard_theme_default'],
        ]);

        Cache::forget('blog:sitemap:xml');
        Cache::forget('blog:rss:xml');

        return redirect()
            ->route('admin.settings.index')
            ->with('status', 'Dashboard settings saved.');
    }
}

