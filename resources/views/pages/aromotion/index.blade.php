@extends('layouts.app')

@section('title', 'AROMOTION Studio | Professional Screen Recording, Smart Motion & Cloud Workspace')
@section('meta_description', 'AROMOTION Studio by AROSOFT combines high-quality Windows screen recording, intelligent cursor motion, zoom, 3D presentation effects, captions and a secure cloud account.')
@section('canonical', route('aromotion.show'))

@section('content')
    <section class="relative overflow-hidden rounded-[2rem] border border-slate-800 bg-[#070b16] text-white shadow-2xl">
        <div class="absolute -left-20 top-12 h-72 w-72 rounded-full bg-violet-600/20 blur-3xl"></div>
        <div class="absolute -right-16 bottom-0 h-80 w-80 rounded-full bg-sky-500/20 blur-3xl"></div>
        <div class="relative grid gap-10 px-7 py-12 sm:px-10 lg:grid-cols-[1.08fr_.92fr] lg:px-14 lg:py-16">
            <div class="flex flex-col justify-center">
                <div class="mb-5 flex flex-wrap items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-slate-300">
                    <span class="rounded-full border border-violet-400/30 bg-violet-400/10 px-3 py-1.5">AROSOFT Solution</span>
                    <span class="rounded-full border border-sky-400/30 bg-sky-400/10 px-3 py-1.5">Windows x64</span>
                    <span class="rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1.5">v{{ $version }} {{ ucfirst($channel) }}</span>
                </div>
                <h1 class="max-w-4xl text-4xl font-black leading-[1.03] tracking-tight sm:text-5xl lg:text-6xl">Record once. Present like a studio.</h1>
                <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">AROMOTION Studio turns ordinary screen recordings into polished tutorials and product demos with clean capture, intelligent cursor presentation, editable motion, click effects, captions, audio controls and a connected AROMOTION Cloud account.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('aromotion.dashboard') }}" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-slate-950 shadow-lg transition hover:-translate-y-0.5">Open Cloud Workspace</a>
                    @else
                        <a href="{{ route('aromotion.account') }}" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-slate-950 shadow-lg transition hover:-translate-y-0.5">Create account & download</a>
                    @endauth
                    <a href="#capabilities" class="rounded-xl border border-slate-700 bg-slate-900/70 px-6 py-3 text-sm font-bold text-white">Explore capabilities</a>
                </div>
                <div class="mt-8 grid max-w-2xl grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach([['60 FPS','Smooth capture'],['FFV1','Lossless option'],['4:4:4','Sharp UI colour'],['Cloud','Account + devices']] as [$value,$label])
                        <div class="rounded-2xl border border-white/10 bg-white/[0.04] p-4 backdrop-blur"><div class="text-lg font-black">{{ $value }}</div><div class="mt-1 text-xs text-slate-400">{{ $label }}</div></div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-center">
                <div class="w-full max-w-xl rounded-[1.8rem] border border-white/10 bg-[#0c1222] p-4 shadow-2xl shadow-violet-950/40">
                    <div class="mb-4 flex items-center justify-between rounded-2xl border border-white/10 bg-[#10182b] px-4 py-3">
                        <div class="flex items-center gap-3"><div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-sky-400 text-sm font-black">AM</div><div><div class="text-sm font-bold">AROMOTION Studio</div><div class="text-[11px] text-slate-400">Motion workspace</div></div></div>
                        <span class="rounded-full border border-emerald-400/25 bg-emerald-400/10 px-3 py-1 text-[10px] font-bold text-emerald-300">Cloud connected</span>
                    </div>
                    <div class="grid gap-3 md:grid-cols-[140px_1fr]">
                        <div class="space-y-2 rounded-2xl border border-white/10 bg-[#0a1020] p-3 text-xs text-slate-400">
                            @foreach(['Recorder','Mouse & Motion','Timeline','Camera','Audio','Captions','Export'] as $item)<div class="rounded-lg px-3 py-2 {{ $loop->first ? 'bg-violet-500/15 font-bold text-white' : '' }}">{{ $item }}</div>@endforeach
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-[#0a1020] p-4">
                            <div class="mb-3 flex items-center justify-between"><span class="text-xs font-bold text-slate-300">Smart motion preview</span><span class="text-[10px] text-slate-500">00:18.42</span></div>
                            <div class="relative aspect-video overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-slate-800 to-slate-950">
                                <div class="absolute left-[18%] top-[20%] h-[56%] w-[64%] rotate-[-2deg] rounded-xl border border-sky-400/30 bg-slate-800/80 shadow-2xl"></div>
                                <div class="absolute left-[56%] top-[43%] h-5 w-5 rounded-full border-2 border-violet-300 bg-violet-400/20 shadow-[0_0_0_12px_rgba(139,92,246,.08)]"></div>
                                <div class="absolute bottom-3 left-3 right-3 flex gap-1"><div class="h-2 flex-1 rounded-full bg-violet-500/70"></div><div class="h-2 w-16 rounded-full bg-sky-400/70"></div><div class="h-2 w-10 rounded-full bg-emerald-400/60"></div></div>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-2 text-center text-[10px] text-slate-400"><div class="rounded-lg border border-white/10 bg-white/[0.03] py-2">Cursor</div><div class="rounded-lg border border-white/10 bg-white/[0.03] py-2">Smart Zoom</div><div class="rounded-lg border border-white/10 bg-white/[0.03] py-2">3D Motion</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="capabilities" class="content-section">
        <div class="mx-auto max-w-3xl text-center"><p class="page-kicker">Built for professional screen communication</p><h2 class="section-title mt-3">A recording tool and presentation engine in one workspace</h2><p class="section-copy mt-4">Capture locally for performance, then use non-destructive motion and a cloud control plane for account, device and project continuity.</p></div>
        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach([
                ['Lossless capture','FFV1 and near-lossless modes preserve text, code, UI edges and fine detail at up to 60 FPS.'],
                ['Smart cursor system','Reconstructed cursor motion, click rings, click sounds and presentation-ready pointer styling.'],
                ['Editable motion','Click-driven zoom, flexible camera framing, easing and 3D presentation motion without touching the clean source master.'],
                ['Dynamic captions','Local transcription workflow with clean, pop and word-highlight caption presentation modes.'],
                ['Audio + camera','Microphone, system audio and webcam controls designed for tutorial and product-demo workflows.'],
                ['AROMOTION Cloud','One account for downloads, device activation, version status and synchronized project metadata.'],
                ['Privacy-first master','The original capture stays clean. Effects are layered non-destructively for safer editing and re-export.'],
                ['Professional export','High-quality MP4 for sharing plus mathematically lossless MKV when absolute fidelity matters.'],
                ['Connected updates','The desktop app can use the cloud manifest and device heartbeat API for controlled releases and support.'],
            ] as [$title,$copy])
                <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"><div class="mb-4 grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-violet-100 to-sky-100 text-sm font-black text-violet-700">AM</div><h3 class="font-heading text-lg text-slate-950">{{ $title }}</h3><p class="mt-2 text-sm leading-7 text-slate-600">{{ $copy }}</p></article>
            @endforeach
        </div>
    </section>

    <section class="content-section overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-950 text-white">
        <div class="grid gap-8 p-8 sm:p-10 lg:grid-cols-2 lg:p-12">
            <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-300">AROMOTION Cloud</p><h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Desktop speed. SaaS control.</h2><p class="mt-4 max-w-2xl text-sm leading-7 text-slate-300">Heavy recording and rendering stays on the user's Windows PC for speed and privacy. AROSOFT Labs hosts the control plane for accounts, devices, product versions and project continuity.</p><div class="mt-7 flex flex-wrap gap-3">@auth<a href="{{ route('aromotion.dashboard') }}" class="rounded-xl bg-sky-400 px-5 py-3 text-sm font-black text-slate-950">Open workspace</a>@else<a href="{{ route('aromotion.account') }}" class="rounded-xl bg-sky-400 px-5 py-3 text-sm font-black text-slate-950">Start with AROMOTION</a>@endauth<a href="{{ route('aromotion.manifest') }}" class="rounded-xl border border-slate-700 px-5 py-3 text-sm font-bold text-white">Release manifest</a></div></div>
            <div class="grid gap-3 sm:grid-cols-2">@foreach([['Account','Secure sign-in and controlled desktop activation.'],['Devices','See the Windows PCs connected to your workspace.'],['Projects','Sync project metadata for continuity and support.'],['Updates','Central release manifest and minimum-version policy.']] as [$title,$copy])<div class="rounded-2xl border border-white/10 bg-white/[0.04] p-5"><div class="font-bold">{{ $title }}</div><p class="mt-2 text-sm leading-6 text-slate-400">{{ $copy }}</p></div>@endforeach</div>
        </div>
    </section>

    <section class="content-section rounded-[2rem] border border-violet-200 bg-gradient-to-br from-violet-50 via-white to-sky-50 p-8 sm:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"><div><p class="page-kicker">Launch edition</p><h2 class="section-title mt-2">Start recording with AROMOTION Studio</h2><p class="section-copy mt-3 max-w-2xl">Create an AROSOFT Labs account, download the Windows build and connect your installation to the cloud workspace.</p></div><div class="flex flex-wrap gap-3">@auth<a href="{{ route('aromotion.dashboard') }}" class="btn-solid">Go to dashboard</a>@else<a href="{{ route('aromotion.account') }}" class="btn-solid">Create account</a>@endauth<a href="{{ route('contact') }}" class="btn-outline">Contact AROSOFT Labs</a></div></div>
    </section>
@endsection
