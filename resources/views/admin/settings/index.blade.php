@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <section class="admin-card p-6">
            <h2 class="font-heading text-2xl">Dashboard Settings</h2>
            <p class="mt-1 text-sm muted-copy">Control admin access and blog runtime behavior without touching deploy files.</p>

            <form action="{{ route('admin.settings.update') }}" method="post" class="mt-6 space-y-5">
                @csrf
                @method('put')

                <div>
                    <label for="admin_emails" class="form-label">Allowed admin emails (comma/newline separated)</label>
                    <textarea id="admin_emails" name="admin_emails" rows="4" class="form-field">{{ old('admin_emails', implode(', ', $settings['admin_emails'] ?? [])) }}</textarea>
                    <p class="mt-1 text-xs muted-faint">Users with these emails can access admin even if their `is_admin` toggle is off.</p>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <div>
                        <label for="sidebar_cache_ttl_minutes" class="form-label">Sidebar cache (min)</label>
                        <input id="sidebar_cache_ttl_minutes" type="number" min="5" max="180" name="sidebar_cache_ttl_minutes" value="{{ old('sidebar_cache_ttl_minutes', $settings['sidebar_cache_ttl_minutes'] ?? 20) }}" class="form-field">
                    </div>
                    <div>
                        <label for="feed_cache_ttl_minutes" class="form-label">Feed cache (min)</label>
                        <input id="feed_cache_ttl_minutes" type="number" min="5" max="180" name="feed_cache_ttl_minutes" value="{{ old('feed_cache_ttl_minutes', $settings['feed_cache_ttl_minutes'] ?? 30) }}" class="form-field">
                    </div>
                    <div>
                        <label for="view_count_window_hours" class="form-label">View dedupe window (hrs)</label>
                        <input id="view_count_window_hours" type="number" min="1" max="48" name="view_count_window_hours" value="{{ old('view_count_window_hours', $settings['view_count_window_hours'] ?? 12) }}" class="form-field">
                    </div>
                </div>

                <div>
                    <label for="dashboard_theme_default" class="form-label">Default dashboard theme</label>
                    <select id="dashboard_theme_default" name="dashboard_theme_default" class="form-field">
                        <option value="light" @selected(old('dashboard_theme_default', $settings['dashboard_theme_default'] ?? 'light') === 'light')>Light</option>
                        <option value="dark" @selected(old('dashboard_theme_default', $settings['dashboard_theme_default'] ?? 'light') === 'dark')>Dark</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-solid !w-auto !px-5 !py-3 !text-[0.66rem]">Save Settings</button>
                    <a href="{{ route('admin.blog.dashboard') }}" class="btn-outline !w-auto !px-5 !py-3 !text-[0.66rem]">Back to Dashboard</a>
                </div>
            </form>
        </section>

        <aside class="space-y-5">
            <section class="admin-card p-5">
                <p class="page-kicker">Quick links</p>
                <div class="mt-3 space-y-2 text-sm">
                    <a href="{{ route('admin.blog.posts.create') }}" class="admin-quick-link">Create New Post</a>
                    <a href="{{ route('admin.users.index') }}" class="admin-quick-link">Manage Users</a>
                    <a href="{{ route('sitemap') }}" target="_blank" class="admin-quick-link">Open Sitemap</a>
                    <a href="{{ route('rss') }}" target="_blank" class="admin-quick-link">Open RSS Feed</a>
                </div>
            </section>

            <section class="admin-card p-5">
                <p class="page-kicker">Deployment note</p>
                <p class="mt-2 text-sm leading-7 muted-copy">
                    Settings here are stored in app cache. They survive requests and can be changed instantly without editing `.env`.
                </p>
            </section>
        </aside>
    </div>
@endsection

