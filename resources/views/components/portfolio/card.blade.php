@props([
    'project',
    'compact' => false,
])

@php
    $statusTone = match (strtolower((string) ($project['status'] ?? 'ready'))) {
        'live' => 'border-[color:rgba(34,197,94,0.25)] bg-[color:rgba(34,197,94,0.12)] text-[color:rgba(21,128,61,0.94)]',
        'in development' => 'border-[color:rgba(245,158,11,0.22)] bg-[color:rgba(245,158,11,0.12)] text-[color:rgba(180,83,9,0.94)]',
        'completed' => 'border-[color:rgba(59,130,246,0.22)] bg-[color:rgba(59,130,246,0.12)] text-[color:rgba(29,78,216,0.94)]',
        default => 'border-[color:rgba(17,24,39,0.12)] bg-[color:rgba(17,24,39,0.06)] text-[color:rgba(17,24,39,0.74)]',
    };
@endphp

<article {{ $attributes->class(['info-card flex h-full flex-col']) }}>
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="page-kicker">{{ $project['category'] }}</p>
            <h3 class="font-heading mt-2 text-2xl leading-tight text-[color:rgba(17,24,39,0.95)]">
                <a href="{{ $project['detail_url'] }}" class="transition hover:text-[var(--accent)]">
                    {{ $project['name'] }}
                </a>
            </h3>
        </div>
        <span class="rounded-full border px-3 py-1 text-[0.62rem] font-semibold uppercase tracking-[0.16em] {{ $statusTone }}">
            {{ $project['status'] }}
        </span>
    </div>

    <div class="mt-5 home-compact-placeholder min-h-[10rem]">
        <span class="home-compact-placeholder-label">{{ $project['year'] }}</span>
    </div>

    <p class="mt-5 text-sm leading-7 muted-copy">
        {{ $project['short_description'] }}
    </p>

    <div class="mt-4 flex flex-wrap gap-2">
        @foreach (array_slice($project['technologies'] ?? [], 0, $compact ? 3 : 4) as $technology)
            <span class="rounded-full border border-[color:rgba(17,24,39,0.12)] bg-[color:rgba(249,251,250,0.95)] px-3 py-1 text-[0.64rem] font-semibold uppercase tracking-[0.08em] text-[color:rgba(17,24,39,0.66)]">
                {{ $technology }}
            </span>
        @endforeach
    </div>

    <div class="mt-auto flex flex-wrap gap-3 pt-6">
        <a href="{{ $project['detail_url'] }}" class="btn-solid {{ $compact ? '!px-4 !py-2' : '' }}">
            View case study
        </a>
        @if (!empty($project['live_url']))
            <a href="{{ $project['live_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-outline {{ $compact ? '!px-4 !py-2' : '' }}">
                Live project
            </a>
        @elseif (!empty($project['demo_url']))
            <a href="{{ $project['demo_url'] }}" target="_blank" rel="noopener noreferrer" class="btn-outline {{ $compact ? '!px-4 !py-2' : '' }}">
                Demo preview
            </a>
        @endif
    </div>
</article>
