<div class="border-b border-[color:rgba(16,24,40,0.12)]">
    <div class="mx-auto flex h-10 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center gap-1.5 sm:gap-3">
            <a href="{{ route('about') }}" class="nav-link-sm">About</a>
            <a href="{{ route('privacy') }}" class="nav-link-sm">Privacy</a>
            <a href="{{ route('contact') }}" class="nav-link-sm">Contact</a>
        </nav>

        @include('layouts.partials.social-icons', [
            'containerClass' => 'flex items-center gap-1.5 sm:gap-2',
            'iconClass' => 'icon-button',
        ])
    </div>
</div>

