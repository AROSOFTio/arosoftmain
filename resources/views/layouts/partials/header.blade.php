<header class="header-shell sticky top-0 z-50 border-b border-[color:rgba(17,24,39,0.1)]" :class="{ 'scrolled': scrolled }">
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="main-nav-band">
            <div class="flex h-20 items-center gap-4 px-4 sm:px-5">
                <a href="{{ route('home') }}" class="group flex shrink-0 items-center leading-none">
                    <span class="brand-logo-wrap">
                        <img src="{{ asset('brand/logo-full.svg') }}" alt="Arosoft Innovations Ltd" class="brand-logo-full">
                    </span>
                </a>

                <nav class="hidden flex-1 items-center justify-center gap-1 lg:flex">
                    <a href="{{ route('home') }}" class="nav-link">Home</a>
                    <a href="{{ route('blog') }}" class="nav-link">Blog</a>

                <div class="relative" @mouseenter="openMega('services')" @mouseleave="closeMega('services')">
                    <button
                        type="button"
                        class="nav-link"
                        @click="toggleMega('services')"
                        @focus="openMega('services')"
                        :aria-expanded="megaOpen === 'services'"
                    >
                        Services
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                            <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="megaOpen === 'services'"
                        @click.outside="closeMega('services')"
                        x-transition:enter="transition duration-180 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition duration-140 ease-in"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="mega-panel absolute left-1/2 top-[calc(100%+0.9rem)] w-[40rem] -translate-x-1/2 p-6"
                    >
                        <p class="text-[0.65rem] uppercase tracking-[0.2em] muted-faint">Service clusters</p>
                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <a href="{{ route('services.printing') }}" class="shell-card rounded-xl p-4 transition duration-200 hover:border-[color:rgba(0,157,49,0.42)]">
                                <p class="font-heading text-lg">Printing</p>
                                <p class="mt-1 text-sm muted-copy">Business cards, branding packs, and premium print runs.</p>
                            </a>
                            <a href="{{ route('services.website-design') }}" class="shell-card rounded-xl p-4 transition duration-200 hover:border-[color:rgba(0,157,49,0.42)]">
                                <p class="font-heading text-lg">Website Design</p>
                                <p class="mt-1 text-sm muted-copy">Conversion-first layouts with high-end visuals.</p>
                            </a>
                            <a href="{{ route('services.web-development') }}" class="shell-card rounded-xl p-4 transition duration-200 hover:border-[color:rgba(0,157,49,0.42)]">
                                <p class="font-heading text-lg">Web Development</p>
                                <p class="mt-1 text-sm muted-copy">Custom Laravel builds and scalable web architecture.</p>
                            </a>
                            <a href="{{ route('services.training-courses') }}" class="shell-card rounded-xl p-4 transition duration-200 hover:border-[color:rgba(0,157,49,0.42)]">
                                <p class="font-heading text-lg">Training/Courses</p>
                                <p class="mt-1 text-sm muted-copy">Hands-on courses for teams and aspiring developers.</p>
                            </a>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('services') }}" class="btn-outline">View services landing</a>
                        </div>
                    </div>
                </div>

                <div class="relative" @mouseenter="openMega('tutorials')" @mouseleave="closeMega('tutorials')">
                    <button
                        type="button"
                        class="nav-link"
                        @click="toggleMega('tutorials')"
                        @focus="openMega('tutorials')"
                        :aria-expanded="megaOpen === 'tutorials'"
                    >
                        Tutorials
                        <svg viewBox="0 0 20 20" fill="none" class="h-4 w-4" aria-hidden="true">
                            <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div
                        x-cloak
                        x-show="megaOpen === 'tutorials'"
                        @click.outside="closeMega('tutorials')"
                        x-transition:enter="transition duration-180 ease-out"
                        x-transition:enter-start="opacity-0 -translate-y-3"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition duration-140 ease-in"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="mega-panel absolute left-1/2 top-[calc(100%+0.9rem)] w-[42rem] -translate-x-1/2 p-6"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-[0.65rem] uppercase tracking-[0.2em] muted-faint">Latest videos</p>
                            <a href="{{ route('tutorials') }}" class="btn-outline">All tutorials</a>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3" x-show="videos.length > 0">
                            <template x-for="video in videos.slice(0, 4)" :key="video.title">
                                <a :href="video.url" class="group overflow-hidden rounded-xl border border-[color:rgba(17,24,39,0.14)] transition duration-200 hover:border-[color:rgba(0,157,49,0.42)]">
                                    <div
                                        class="h-24 w-full border-b border-[color:rgba(17,24,39,0.1)] bg-[color:rgba(255,255,255,0.94)] bg-cover bg-center"
                                        :style="`background-image:url('${video.thumb}')`"
                                    ></div>
                                    <div class="space-y-1 p-3">
                                        <p class="text-sm font-semibold leading-snug text-[color:rgba(17,24,39,0.92)]" x-text="video.title"></p>
                                        <p class="text-[0.65rem] uppercase tracking-[0.16em] muted-faint" x-text="video.date"></p>
                                    </div>
                                </a>
                            </template>
                        </div>
                        <p x-show="videos.length === 0" class="mt-4 rounded-lg border border-[color:rgba(17,24,39,0.12)] px-3 py-2 text-sm muted-copy">
                            No recent tutorial videos available right now.
                        </p>

                        <div class="mt-5 border-t border-[color:rgba(17,24,39,0.08)] pt-4">
                            <p class="text-[0.65rem] uppercase tracking-[0.2em] muted-faint">Popular playlists</p>
                            <div class="mt-3 grid grid-cols-2 gap-3" x-show="playlists.length > 0">
                                <template x-for="playlist in playlists.slice(0, 4)" :key="playlist.playlist_id || playlist.url">
                                    <a :href="playlist.url" class="group overflow-hidden rounded-xl border border-[color:rgba(17,24,39,0.14)] transition duration-200 hover:border-[color:rgba(0,157,49,0.42)]">
                                        <div
                                            class="h-20 w-full border-b border-[color:rgba(17,24,39,0.1)] bg-[color:rgba(240,247,243,0.86)] bg-cover bg-center"
                                            :style="playlist.thumb ? `background-image:url('${playlist.thumb}')` : ''"
                                        ></div>
                                        <div class="space-y-1 p-3">
                                            <p class="text-sm font-semibold leading-snug text-[color:rgba(17,24,39,0.92)]" x-text="playlist.title"></p>
                                            <p class="text-[0.65rem] uppercase tracking-[0.16em] muted-faint" x-text="playlist.meta || 'Playlist'"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <p x-show="playlists.length === 0" class="mt-3 rounded-lg border border-[color:rgba(17,24,39,0.12)] px-3 py-2 text-sm muted-copy">
                                No playlists available right now.
                            </p>
                        </div>
                    </div>
                </div>

                    <a href="{{ route('tools') }}" class="nav-link">Tools</a>
                </nav>

                <div class="ml-auto hidden items-center gap-3 lg:flex">
                    <div class="relative w-80" @click.outside="closeSearch()">
                        <svg viewBox="0 0 24 24" fill="none" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:rgba(17,24,39,0.45)]" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <input
                            type="search"
                            class="search-input"
                            placeholder="Search blog, courses, tools, services..."
                            x-model="searchQuery"
                            @focus="focusSearch()"
                            @input.debounce.160ms="updateSearch()"
                            @keydown.escape.stop.prevent="closeSearch()"
                        >
                        @include('layouts.partials.search-dropdown')
                    </div>

                    <button type="button" class="btn-outline" @click="openOffcanvas()">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M10 17h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                        Menu
                    </button>
                </div>

                <div class="ml-auto flex items-center gap-2 lg:hidden">
                    <button type="button" class="icon-button" @click="toggleMobileSearch()" aria-label="Toggle search">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                            <circle cx="11" cy="11" r="6.2" stroke="currentColor" stroke-width="1.8"/>
                            <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <button type="button" class="icon-button" @click="openOffcanvas()" aria-label="Open menu">
                        <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M10 17h10" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-4 pb-4 sm:px-5 lg:hidden" x-cloak x-show="mobileSearchOpen" x-transition>
                <div class="relative" @click.outside="closeSearch()">
                    <svg viewBox="0 0 24 24" fill="none" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[color:rgba(17,24,39,0.45)]" aria-hidden="true">
                        <circle cx="11" cy="11" r="6.2" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m16 16 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    <input
                        x-ref="mobileSearchInput"
                        type="search"
                        class="search-input"
                        placeholder="Search blog, courses, tools, services..."
                        x-model="searchQuery"
                        @focus="focusSearch()"
                        @input.debounce.160ms="updateSearch()"
                        @keydown.escape.stop.prevent="closeSearch()"
                    >
                    @include('layouts.partials.search-dropdown')
                </div>
            </div>
        </div>
    </div>
</header>

