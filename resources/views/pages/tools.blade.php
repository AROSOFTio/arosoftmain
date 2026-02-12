@extends('layouts.app')

@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@section('canonical', $canonicalUrl)

@section('schema')
    <script type="application/ld+json">
        {!! json_encode($toolSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($catalogSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endsection

@section('content')
    @if (session('tool_status'))
        <div class="alert-success">
            {{ session('tool_status') }}
        </div>
    @endif

    @if (session('tool_error'))
        <div class="mt-4 rounded-xl border border-[color:rgba(17,24,39,0.16)] bg-[color:rgba(0,157,49,0.08)] p-4 text-sm text-[color:rgba(17,24,39,0.92)]">
            {{ session('tool_error') }}
        </div>
    @endif

    <section class="hero-surface p-8 sm:p-10 lg:p-12">
        <div class="hero-grid">
            <div>
                <p class="page-kicker">IT Tools Directory</p>
                <h1 class="page-title mt-4">Practical online tools for documents, Excel, OCR, and data workflows</h1>
                <p class="section-copy mt-5 max-w-3xl">
                    Browse by category, click a tool on the left, and review details plus processing workspace on the right.
                    Each tool has a dedicated URL for indexing, SEO, and AI discoverability.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="#tool-layout" class="btn-solid">Browse IT tools</a>
                    <a href="{{ route('contact') }}" class="btn-outline">Request another tool</a>
                </div>
            </div>

            <aside class="grid gap-3">
                <article class="feature-tile">
                    <h3 class="font-heading">Categorized tool navigation</h3>
                    <p>Document, spreadsheet, OCR, and data utilities are grouped for faster access.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Dedicated SEO URLs</h3>
                    <p>Every tool opens on its own route for stronger indexing and sharing.</p>
                </article>
                <article class="feature-tile">
                    <h3 class="font-heading">Right-side processing panel</h3>
                    <p>Each selected tool includes action buttons and a ready-to-use processing area.</p>
                </article>
            </aside>
        </div>
    </section>

    <section id="tool-layout" class="content-section grid gap-6 xl:grid-cols-12">
        <aside class="info-card h-fit xl:sticky xl:top-28 xl:col-span-4">
            <p class="page-kicker">Tool Categories</p>
            <h2 class="font-heading mt-2 text-2xl">All IT tools ({{ $toolCount }})</h2>

            <div class="mt-5 space-y-4">
                @foreach ($categories as $category)
                    <section class="rounded-xl border border-[color:rgba(17,24,39,0.12)] p-3">
                        <h3 class="font-heading text-lg">{{ $category['name'] }}</h3>
                        <p class="mt-1 text-xs leading-6 muted-faint">{{ $category['summary'] }}</p>

                        <nav class="mt-3 space-y-2">
                            @foreach ($category['tools'] as $tool)
                                <a
                                    href="{{ route('tools.show', ['slug' => $tool['slug']]) }}"
                                    class="flex items-center justify-between rounded-lg border px-3 py-2 text-sm transition {{ $activeTool['slug'] === $tool['slug'] ? 'border-[color:rgba(0,157,49,0.42)] bg-[color:rgba(0,157,49,0.08)]' : 'border-[color:rgba(17,24,39,0.12)] hover:border-[color:rgba(0,157,49,0.4)] hover:bg-[color:rgba(0,157,49,0.05)]' }}"
                                >
                                    <span class="font-semibold text-[color:rgba(17,24,39,0.9)]">{{ $tool['name'] }}</span>
                                    <span class="text-[0.62rem] uppercase tracking-[0.12em] {{ $tool['status'] === 'Live' ? 'text-[var(--accent)]' : 'muted-faint' }}">
                                        {{ $tool['status'] }}
                                    </span>
                                </a>
                            @endforeach
                        </nav>
                    </section>
                @endforeach
            </div>
        </aside>

        <div class="space-y-6 xl:col-span-8">
            <article class="info-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="page-kicker">{{ $activeTool['category_name'] }}</p>
                        <h2 class="font-heading mt-2 text-3xl">{{ $activeTool['name'] }}</h2>
                        <p class="mt-3 text-sm leading-7 muted-copy">{{ $activeTool['tagline'] }}</p>
                    </div>
                    <span class="rounded-full border border-[color:rgba(0,157,49,0.35)] px-3 py-1 text-[0.65rem] uppercase tracking-[0.12em] text-[var(--accent)]">
                        {{ $activeTool['status'] }}
                    </span>
                </div>

                <p class="mt-4 text-sm leading-7 muted-copy">{{ $activeTool['description'] }}</p>

                <div class="mt-4 rounded-lg border border-[color:rgba(17,24,39,0.12)] bg-[color:rgba(0,157,49,0.06)] px-3 py-2">
                    <p class="text-[0.62rem] uppercase tracking-[0.15em] muted-faint">Tool URL</p>
                    <p class="mt-1 text-sm font-semibold break-all text-[color:rgba(17,24,39,0.92)]">{{ route('tools.show', ['slug' => $activeTool['slug']]) }}</p>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="#tool-workspace" class="btn-solid">Process on right panel</a>
                    <a href="{{ route('tools.show', ['slug' => $activeTool['slug']]) }}" class="btn-outline">Open dedicated URL</a>
                </div>

                @if (!empty($activeTool['use_cases']))
                    <div class="mt-6">
                        <h3 class="font-heading text-lg">Use cases</h3>
                        <ul class="list-check mt-2">
                            @foreach ($activeTool['use_cases'] as $useCase)
                                <li>{{ $useCase }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </article>

            <article id="tool-workspace" class="info-card">
                <h3 class="section-title">Tool Workspace</h3>
                <p class="mt-3 text-sm leading-7 muted-copy">
                    Select inputs for <strong>{{ $activeTool['name'] }}</strong> and run processing from this panel.
                </p>

                @if ($activeTool['input_type'] === 'text')
                    <form action="{{ route('tools.process', ['slug' => $activeTool['slug']]) }}" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="form-label" for="text_payload">Paste text input</label>
                            <textarea id="text_payload" name="text_payload" rows="9" class="form-field @error('text_payload') border-[color:rgba(0,157,49,0.85)] @enderror" placeholder="Paste your payload here...">{{ old('text_payload') }}</textarea>
                            @error('text_payload')
                                <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="btn-solid">{{ $activeTool['button_label'] }}</button>
                        <p class="text-xs uppercase tracking-[0.12em] text-[var(--accent)]">Mode: instant</p>
                    </form>

                    @if (session('tool_result'))
                        <div class="mt-5 rounded-xl border border-[color:rgba(17,24,39,0.12)] bg-[color:rgba(17,24,39,0.03)] p-4">
                            <p class="text-[0.65rem] uppercase tracking-[0.14em] muted-faint">{{ session('tool_result_label', 'Result') }}</p>
                            <pre class="mt-3 overflow-x-auto whitespace-pre-wrap text-sm leading-7 text-[color:rgba(17,24,39,0.92)]">{{ session('tool_result') }}</pre>
                        </div>
                    @endif
                @else
                    <form action="{{ route('tools.process', ['slug' => $activeTool['slug']]) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label class="form-label" for="upload_file">Upload file</label>
                            <input id="upload_file" type="file" name="upload_file" class="form-field @error('upload_file') border-[color:rgba(0,157,49,0.85)] @enderror" accept="{{ $activeTool['accept'] }}">
                            @error('upload_file')
                                <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs muted-faint">Accepted input: {{ $activeTool['accept'] ?: 'File upload' }}</p>
                        </div>

                        <div>
                            <label class="form-label" for="processing_note">Optional processing note</label>
                            <textarea id="processing_note" name="processing_note" rows="4" class="form-field" placeholder="Add output preferences, page range, or formatting notes...">{{ old('processing_note') }}</textarea>
                        </div>

                        <button type="submit" class="btn-solid">{{ $activeTool['button_label'] }}</button>
                        <p class="text-xs uppercase tracking-[0.12em] text-[var(--accent)]">Mode: {{ $activeTool['processing_mode'] }}</p>
                    </form>
                @endif
            </article>
        </div>
    </section>
@endsection
