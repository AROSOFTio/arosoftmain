@extends('layouts.app')

@section('title', 'AROMOTION Cloud Workspace | AROSOFT Labs')
@section('meta_description', 'Manage your AROMOTION Studio download, connected Windows devices, cloud project metadata and release status.')
@section('canonical', route('aromotion.dashboard'))

@section('content')
    <section class="rounded-[2rem] border border-slate-800 bg-[#070b16] p-7 text-white shadow-2xl sm:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex items-center gap-3"><div class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-sky-400 text-sm font-black">AM</div><div><p class="text-xs font-bold uppercase tracking-[0.16em] text-sky-300">AROMOTION Cloud</p><h1 class="text-2xl font-black sm:text-3xl">Welcome, {{ auth()->user()->name }}</h1></div></div>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Your Windows build, connected devices, project metadata and product release status are managed here.</p>
            </div>
            <form method="POST" action="{{ route('aromotion.logout') }}">@csrf<button class="rounded-xl border border-slate-700 px-4 py-2.5 text-sm font-bold text-white hover:border-slate-500">Sign out</button></form>
        </div>
    </section>

    @if(session('status'))<div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('status') }}</div>@endif
    @if(session('download_error'))<div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">{{ session('download_error') }}</div>@endif

    <section class="content-section grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Plan</div><div class="mt-2 text-xl font-black text-slate-950">{{ ucfirst($subscription->plan) }}</div><div class="mt-1 text-sm font-semibold {{ $subscription->status === 'active' ? 'text-emerald-600' : 'text-amber-600' }}">{{ ucfirst($subscription->status) }}</div></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Latest build</div><div class="mt-2 text-xl font-black text-slate-950">v{{ $version }}</div><div class="mt-1 text-sm text-slate-500">{{ ucfirst($channel) }} channel</div></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Devices</div><div class="mt-2 text-xl font-black text-slate-950">{{ $devices->count() }}</div><div class="mt-1 text-sm text-slate-500">Connected Windows PCs</div></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><div class="text-xs font-bold uppercase tracking-[.14em] text-slate-400">Projects</div><div class="mt-2 text-xl font-black text-slate-950">{{ $projects->count() }}</div><div class="mt-1 text-sm text-slate-500">Synced project records</div></article>
    </section>

    <section class="content-section grid gap-5 lg:grid-cols-[.9fr_1.1fr]">
        <article class="overflow-hidden rounded-[1.6rem] border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-6"><p class="page-kicker">Windows app</p><h2 class="mt-2 text-2xl font-black text-slate-950">Download AROMOTION Studio</h2><p class="mt-2 text-sm leading-6 text-slate-600">Windows 10/11 x64. Your account is used to activate the desktop client and connect it to this workspace.</p></div>
            <div class="p-6"><a href="{{ route('aromotion.download.windows') }}" class="block rounded-xl bg-gradient-to-r from-violet-600 to-sky-500 px-6 py-3 text-center text-sm font-black text-white shadow-lg shadow-violet-100">Download Windows v{{ $version }}</a><div class="mt-4 grid grid-cols-2 gap-3 text-xs text-slate-500"><div class="rounded-xl bg-slate-50 p-3"><span class="block font-bold text-slate-800">Platform</span>Windows x64</div><div class="rounded-xl bg-slate-50 p-3"><span class="block font-bold text-slate-800">Release</span>{{ ucfirst($channel) }}</div></div></div>
        </article>

        <article class="rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4"><div><p class="page-kicker">Connected devices</p><h2 class="mt-2 text-2xl font-black text-slate-950">Your installations</h2></div><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $devices->count() }} total</span></div>
            <div class="mt-5 space-y-3">
                @forelse($devices as $device)
                    <div class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 p-4"><div class="min-w-0"><div class="truncate text-sm font-bold text-slate-950">{{ $device->device_name ?: 'Windows PC' }}</div><div class="mt-1 text-xs text-slate-500">{{ $device->platform }} · v{{ $device->app_version ?: '—' }} · {{ $device->last_seen_at?->diffForHumans() ?: 'Not seen yet' }}</div></div><span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $device->revoked_at ? 'bg-red-400' : 'bg-emerald-400' }}"></span></div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm leading-6 text-slate-500">No desktop installation is connected yet. Download AROMOTION and sign in from the app to activate this workspace.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="content-section rounded-[1.6rem] border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"><div><p class="page-kicker">Cloud projects</p><h2 class="mt-2 text-2xl font-black text-slate-950">Recent project metadata</h2><p class="mt-2 text-sm text-slate-500">Desktop projects appear here when the AROMOTION client syncs them.</p></div><a href="{{ route('aromotion.manifest') }}" class="text-sm font-bold text-violet-700">View release API →</a></div>
        <div class="mt-5 overflow-x-auto"><table class="w-full min-w-[640px] text-left text-sm"><thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-400"><tr><th class="py-3 pr-4">Project</th><th class="py-3 pr-4">Status</th><th class="py-3 pr-4">Duration</th><th class="py-3 pr-4">Version</th><th class="py-3">Last sync</th></tr></thead><tbody class="divide-y divide-slate-100">@forelse($projects as $project)<tr><td class="py-4 pr-4 font-bold text-slate-900">{{ $project->name }}</td><td class="py-4 pr-4 text-slate-600">{{ ucfirst($project->status) }}</td><td class="py-4 pr-4 text-slate-600">{{ gmdate('H:i:s', intdiv($project->duration_ms, 1000)) }}</td><td class="py-4 pr-4 text-slate-600">{{ $project->app_version ?: '—' }}</td><td class="py-4 text-slate-500">{{ $project->last_synced_at?->diffForHumans() ?: '—' }}</td></tr>@empty<tr><td colspan="5" class="py-8 text-center text-slate-500">No projects synced yet.</td></tr>@endforelse</tbody></table></div>
    </section>
@endsection
