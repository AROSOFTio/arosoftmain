<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Blog Admin') | Arosoft</title>
        <meta name="robots" content="noindex,nofollow">
        @include('layouts.partials.favicons')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
        <script>
            window.adminUi = () => ({
                sidebarCollapsed: false,
                mobileSidebarOpen: false,
                darkMode: false,
                groups: {
                    content: true,
                    people: true,
                    system: true,
                },
                init() {
                    const theme = localStorage.getItem('admin-theme');
                    const collapsed = localStorage.getItem('admin-sidebar-collapsed');
                    const groups = localStorage.getItem('admin-sidebar-groups');
                    const defaultTheme = @json(\App\Support\AdminSettings::get('dashboard_theme_default', 'light'));

                    this.darkMode = theme ? theme === 'dark' : defaultTheme === 'dark';
                    this.sidebarCollapsed = collapsed === 'true' && window.innerWidth >= 1024;

                    if (groups) {
                        try {
                            this.groups = { ...this.groups, ...JSON.parse(groups) };
                        } catch (error) {
                            // no-op
                        }
                    }
                },
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('admin-theme', this.darkMode ? 'dark' : 'light');
                },
                toggleSidebar() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                    localStorage.setItem('admin-sidebar-collapsed', this.sidebarCollapsed ? 'true' : 'false');
                },
                toggleGroup(group) {
                    this.groups[group] = !this.groups[group];
                    localStorage.setItem('admin-sidebar-groups', JSON.stringify(this.groups));
                },
            });
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('head')
    </head>
    <body class="admin-shell antialiased" x-data="adminUi()" x-init="init()" :class="{ 'admin-dark': darkMode }">
        <div class="admin-viewport">
            <div
                x-cloak
                x-show="mobileSidebarOpen"
                @click="mobileSidebarOpen = false"
                class="admin-sidebar-overlay lg:hidden"
            ></div>

            <aside class="admin-sidebar" :class="{ 'is-collapsed': sidebarCollapsed, 'is-mobile-open': mobileSidebarOpen }">
                <div class="admin-sidebar-head">
                    <a href="{{ route('admin.blog.dashboard') }}" class="admin-brand-link">
                        <span class="admin-brand-icon" aria-hidden="true">
                            <img src="{{ asset('brand/logo-mark.svg') }}" alt="" class="admin-brand-icon-img">
                        </span>
                        <div class="admin-brand-copy" x-show="!sidebarCollapsed">
                            <p class="admin-brand-title">Arosoft Admin</p>
                            <p class="admin-brand-subtitle">Content Control</p>
                        </div>
                    </a>
                    <button type="button" class="admin-icon-btn hidden lg:inline-flex" @click="toggleSidebar()" aria-label="Collapse sidebar">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M14 6 8 12l6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <button type="button" class="admin-icon-btn lg:hidden" @click="mobileSidebarOpen = false" aria-label="Close sidebar">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <nav class="admin-sidebar-nav">
                    <a
                        href="{{ route('admin.blog.dashboard') }}"
                        class="admin-nav-link"
                        :class="{ 'is-active': {{ request()->routeIs('admin.blog.dashboard') ? 'true' : 'false' }} }"
                    >
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M4 11.2 12 4l8 7.2V20H4v-8.8Z" stroke="currentColor" stroke-width="1.7"/>
                        </svg>
                        <span x-show="!sidebarCollapsed">Dashboard</span>
                    </a>

                    <div class="admin-nav-group">
                        <button type="button" class="admin-nav-link admin-nav-group-trigger" @click="toggleGroup('content')">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M5 6h14M5 12h14M5 18h14" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                            <span x-show="!sidebarCollapsed">Content</span>
                            <svg x-show="!sidebarCollapsed" viewBox="0 0 20 20" fill="none" class="h-4 w-4 ml-auto" :class="{ 'rotate-180': groups.content }">
                                <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div x-show="groups.content || sidebarCollapsed" x-cloak class="admin-subnav">
                            <a href="{{ route('admin.blog.posts.index') }}" class="admin-subnav-link {{ request()->routeIs('admin.blog.posts.index') ? 'is-active' : '' }}">
                                <span x-show="!sidebarCollapsed">All Posts</span>
                                <span x-show="sidebarCollapsed">P</span>
                            </a>
                            <a href="{{ route('admin.blog.posts.create') }}" class="admin-subnav-link {{ request()->routeIs('admin.blog.posts.create') ? 'is-active' : '' }}">
                                <span x-show="!sidebarCollapsed">Create Post</span>
                                <span x-show="sidebarCollapsed">+</span>
                            </a>
                        </div>
                    </div>

                    <div class="admin-nav-group">
                        <button type="button" class="admin-nav-link admin-nav-group-trigger" @click="toggleGroup('people')">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M16 19v-1.5a3.5 3.5 0 0 0-3.5-3.5H8.8a3.8 3.8 0 0 0-3.8 3.8V19M14.8 8.5a2.8 2.8 0 1 1-5.6 0 2.8 2.8 0 0 1 5.6 0ZM19 19v-1a2.8 2.8 0 0 0-2.3-2.8M15.5 6.3a2.6 2.6 0 0 1 0 5.1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                            </svg>
                            <span x-show="!sidebarCollapsed">People</span>
                            <svg x-show="!sidebarCollapsed" viewBox="0 0 20 20" fill="none" class="h-4 w-4 ml-auto" :class="{ 'rotate-180': groups.people }">
                                <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div x-show="groups.people || sidebarCollapsed" x-cloak class="admin-subnav">
                            <a href="{{ route('admin.users.index') }}" class="admin-subnav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                                <span x-show="!sidebarCollapsed">Users</span>
                                <span x-show="sidebarCollapsed">U</span>
                            </a>
                        </div>
                    </div>

                    <div class="admin-nav-group">
                        <button type="button" class="admin-nav-link admin-nav-group-trigger" @click="toggleGroup('system')">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M10.6 4.4h2.8l.5 2a6 6 0 0 1 1.4.8l2-.8 1.4 2.4-1.5 1.4c.1.4.2.9.2 1.4s0 1-.2 1.4l1.5 1.4-1.4 2.4-2-.8a6 6 0 0 1-1.4.8l-.5 2h-2.8l-.5-2a6 6 0 0 1-1.4-.8l-2 .8-1.4-2.4 1.5-1.4A5.7 5.7 0 0 1 6 12c0-.5 0-1 .2-1.4L4.7 9.2l1.4-2.4 2 .8c.4-.3.9-.6 1.4-.8l.5-2Z" stroke="currentColor" stroke-width="1.4"/>
                                <circle cx="12" cy="12" r="2.2" stroke="currentColor" stroke-width="1.4"/>
                            </svg>
                            <span x-show="!sidebarCollapsed">System</span>
                            <svg x-show="!sidebarCollapsed" viewBox="0 0 20 20" fill="none" class="h-4 w-4 ml-auto" :class="{ 'rotate-180': groups.system }">
                                <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <div x-show="groups.system || sidebarCollapsed" x-cloak class="admin-subnav">
                            <a href="{{ route('admin.settings.index') }}" class="admin-subnav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
                                <span x-show="!sidebarCollapsed">Settings</span>
                                <span x-show="sidebarCollapsed">S</span>
                            </a>
                            <a href="{{ route('sitemap') }}" target="_blank" class="admin-subnav-link">
                                <span x-show="!sidebarCollapsed">Sitemap</span>
                                <span x-show="sidebarCollapsed">M</span>
                            </a>
                            <a href="{{ route('rss') }}" target="_blank" class="admin-subnav-link">
                                <span x-show="!sidebarCollapsed">RSS Feed</span>
                                <span x-show="sidebarCollapsed">R</span>
                            </a>
                        </div>
                    </div>
                </nav>

                <div class="admin-sidebar-foot">
                    <a href="{{ route('blog') }}" class="admin-nav-link">
                        <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M4 12h16M12 4l8 8-8 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span x-show="!sidebarCollapsed">View Public Site</span>
                    </a>
                </div>
            </aside>

            <div class="admin-main-wrap">
                <header class="admin-topbar">
                    <div class="admin-topbar-left">
                        <button type="button" class="admin-icon-btn lg:hidden" @click="mobileSidebarOpen = true" aria-label="Open sidebar">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div>
                            <p class="admin-page-label">Admin Area</p>
                            <h1 class="admin-page-title">@yield('title', 'Dashboard')</h1>
                        </div>
                    </div>
                    <div class="admin-topbar-actions">
                        <button type="button" class="admin-icon-btn" @click="toggleTheme()" :title="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                            <svg x-show="!darkMode" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M12 3v2.2M12 18.8V21M5.6 5.6l1.5 1.5M16.9 16.9l1.5 1.5M3 12h2.2M18.8 12H21M5.6 18.4l1.5-1.5M16.9 7.1l1.5-1.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.6"/>
                            </svg>
                            <svg x-show="darkMode" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M20.3 14.8A8.5 8.5 0 1 1 9.2 3.7a7 7 0 0 0 11 11Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                        <a href="{{ route('admin.blog.posts.create') }}" class="btn-solid !w-auto !px-4 !py-2 !text-[0.66rem]">New Post</a>
                        <form action="{{ route('admin.logout') }}" method="post">
                            @csrf
                            <button type="submit" class="btn-outline !w-auto !px-3 !py-2 !text-[0.62rem]">Logout</button>
                        </form>
                    </div>
                </header>

                <main class="admin-main-content">
                    @if(session('status'))
                        <div class="alert-success mb-6">{{ session('status') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-xl border border-[color:rgba(185,28,28,0.3)] bg-[color:rgba(185,28,28,0.07)] px-4 py-3 text-sm text-[color:rgba(127,29,29,0.95)]">
                            <p class="font-semibold">Please fix the highlighted fields.</p>
                        </div>
                    @endif

                    @yield('content')
                </main>

                <footer class="admin-footer">
                    <p>© {{ now()->year }} Arosoft Innovations Ltd</p>
                    <p>Blog CMS dashboard with publishing, users, SEO, and system controls.</p>
                </footer>
            </div>
        </div>
    </body>
</html>
