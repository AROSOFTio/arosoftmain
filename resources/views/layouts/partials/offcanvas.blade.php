<div x-cloak x-show="offcanvasOpen" class="fixed inset-0 z-[80]" aria-modal="true" role="dialog">
    <div
        class="absolute inset-0 bg-[color:rgba(17,24,39,0.16)] backdrop-blur-sm"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeOffcanvas()"
    ></div>

    <aside
        x-ref="offcanvasPanel"
        tabindex="-1"
        @keydown.tab.prevent="trapOffcanvasFocus($event)"
        x-transition:enter="transition transform duration-260 ease-out"
        x-transition:enter-start="opacity-0 translate-x-full"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition transform duration-180 ease-in"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 translate-x-full"
        class="offcanvas-panel absolute right-0 top-0 flex h-full w-full max-w-md flex-col border-l p-6"
    >
        <div class="flex items-center justify-between">
            <p class="font-heading text-lg uppercase tracking-[0.2em]">Menu</p>
            <button type="button" class="icon-button" @click="closeOffcanvas()" aria-label="Close menu">
                <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4" aria-hidden="true">
                    <path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <nav class="mt-8 space-y-2">
            <a href="{{ route('home') }}" class="btn-outline w-full justify-between" @click="closeOffcanvas()">
                Home
                <span class="muted-faint">/</span>
            </a>
            <a href="{{ route('blog') }}" class="btn-outline w-full justify-between" @click="closeOffcanvas()">
                Blog
                <span class="muted-faint">/blog</span>
            </a>
            <a href="{{ route('services') }}" class="btn-outline w-full justify-between" @click="closeOffcanvas()">
                Services
                <span class="muted-faint">/services</span>
            </a>
            <a href="{{ route('tutorials') }}" class="btn-outline w-full justify-between" @click="closeOffcanvas()">
                Tutorials/Videos
                <span class="muted-faint">/tutorials</span>
            </a>
            <a href="{{ route('tools') }}" class="btn-outline w-full justify-between" @click="closeOffcanvas()">
                IT Tools
                <span class="muted-faint">/tools</span>
            </a>
        </nav>

        <section class="mt-8">
            <p class="text-[0.66rem] uppercase tracking-[0.2em] muted-faint">Services</p>
            <div class="mt-3 space-y-2">
                <a href="{{ route('services.printing') }}" class="shell-card block rounded-xl px-4 py-3 text-sm font-semibold" @click="closeOffcanvas()">Printing</a>
                <a href="{{ route('services.website-design') }}" class="shell-card block rounded-xl px-4 py-3 text-sm font-semibold" @click="closeOffcanvas()">Website Design</a>
                <a href="{{ route('services.web-development') }}" class="shell-card block rounded-xl px-4 py-3 text-sm font-semibold" @click="closeOffcanvas()">Web Development</a>
                <a href="{{ route('services.training-courses') }}" class="shell-card block rounded-xl px-4 py-3 text-sm font-semibold" @click="closeOffcanvas()">Training/Courses</a>
            </div>
        </section>

        <section class="mt-8">
            <p class="text-[0.66rem] uppercase tracking-[0.2em] muted-faint">Latest tutorials</p>
            <div class="mt-3 space-y-2">
                <template x-for="video in videos.slice(0, 4)" :key="video.title">
                    <a :href="video.url" class="group flex items-center gap-3 rounded-xl border border-[color:rgba(255,255,255,0.32)] p-2 transition duration-200 hover:border-[color:rgba(255,255,255,0.62)]" @click="closeOffcanvas()">
                        <div
                            class="h-12 w-20 flex-none rounded-lg border border-[color:rgba(255,255,255,0.34)] bg-[color:rgba(255,255,255,0.18)] bg-cover bg-center"
                            :style="`background-image:url('${video.thumb}')`"
                        ></div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-white" x-text="video.title"></p>
                            <p class="text-[0.62rem] uppercase tracking-[0.15em] text-[color:rgba(255,255,255,0.72)]" x-text="video.date"></p>
                        </div>
                    </a>
                </template>
            </div>
        </section>

        <div class="mt-auto space-y-4 pt-8">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('about') }}" class="nav-link-sm" @click="closeOffcanvas()">About</a>
                <a href="{{ route('privacy') }}" class="nav-link-sm" @click="closeOffcanvas()">Privacy</a>
                <a href="{{ route('contact') }}" class="nav-link-sm" @click="closeOffcanvas()">Contact</a>
            </div>

            @include('layouts.partials.social-icons', [
                'containerClass' => 'flex items-center gap-2',
                'iconClass' => 'icon-button',
            ])
        </div>
    </aside>
</div>

