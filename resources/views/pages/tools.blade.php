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
    @php
        $textPlaceholder = match ($activeTool['processor'] ?? '') {
            'qr_link' => 'Paste URL or text...',
            'hash_sha256' => 'Paste text for SHA-256 hash...',
            'json_formatter' => 'Paste JSON payload...',
            'base64_encode' => 'Paste text to encode...',
            'youtube_downloader' => 'Paste YouTube URL (watch, shorts, embed, or youtu.be)...',
            default => 'Paste text input...',
        };

        $toolIconTone = static function (string $slug): string {
            if (str_contains($slug, 'pdf-to-')) {
                return 'tool-nav-icon--blue';
            }

            if (str_contains($slug, 'youtube') || str_contains($slug, 'download')) {
                return 'tool-nav-icon--red';
            }

            if (str_contains($slug, '-to-pdf') || str_contains($slug, 'scan-to-pdf')) {
                return 'tool-nav-icon--green';
            }

            if (str_contains($slug, 'image') || str_contains($slug, 'jpg') || str_contains($slug, 'png') || str_contains($slug, 'tiff') || str_contains($slug, 'svg')) {
                return 'tool-nav-icon--teal';
            }

            return 'tool-nav-icon--amber';
        };

        $toolIconLabel = static function (array $tool): string {
            $slug = strtolower((string) ($tool['slug'] ?? ''));

            if (str_contains($slug, 'pdf')) {
                return 'PDF';
            }

            if (str_contains($slug, 'word') || str_contains($slug, 'doc')) {
                return 'DOC';
            }

            if (str_contains($slug, 'excel') || str_contains($slug, 'xls')) {
                return 'XLS';
            }

            if (str_contains($slug, 'image') || str_contains($slug, 'jpg') || str_contains($slug, 'png') || str_contains($slug, 'tiff') || str_contains($slug, 'svg')) {
                return 'IMG';
            }

            if (str_contains($slug, 'hash') || str_contains($slug, 'uuid') || str_contains($slug, 'json') || str_contains($slug, 'base64') || str_contains($slug, 'qr')) {
                return 'GEN';
            }

            if (str_contains($slug, 'youtube')) {
                return 'YT';
            }

            if (str_contains($slug, 'download')) {
                return 'DL';
            }

            $name = trim((string) ($tool['name'] ?? 'Tool'));
            $parts = explode(' ', $name);
            $head = $parts[0] ?? 'Tool';
            $normalized = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $head), 0, 2));

            return $normalized !== '' ? $normalized : 'TOOL';
        };

        $categoryIconTone = static function (string $key): string {
            return match ($key) {
                'converter' => 'tool-group-icon--green',
                'password-remover' => 'tool-group-icon--amber',
                'generators' => 'tool-group-icon--blue',
                'downloaders' => 'tool-group-icon--red',
                default => 'tool-group-icon--teal',
            };
        };

        $categoryIconLabel = static function (string $key): string {
            return match ($key) {
                'converter' => 'CV',
                'password-remover' => 'PW',
                'generators' => 'GN',
                'downloaders' => 'DL',
                default => 'TL',
            };
        };
    @endphp

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

    <div class="mt-6">
        <x-adsense.unit
            slot="8082861654"
            format="auto"
            :full-width-responsive="true"
            style="display:block"
            wrapperClass="info-card"
        />
    </div>

    <section class="overflow-hidden rounded-2xl border border-[color:rgba(17,24,39,0.18)] bg-white shadow-[0_12px_26px_rgba(17,24,39,0.06)]">
        <div class="h-4 bg-[var(--accent)]"></div>

        <header class="border-b border-[color:rgba(17,24,39,0.18)] px-5 py-6 text-center sm:px-8">
            <h1 class="font-heading text-3xl leading-tight text-[color:rgba(17,24,39,0.95)] sm:text-4xl">Welcome to Arosoft Tools Arena</h1>
            <p class="mt-3 text-sm leading-7 muted-copy">
                Choose a tool category on the left. Tool details and processing workspace appear on the right.
                Each tool has a dedicated URL for indexing, SEO, and AI optimization.
            </p>
        </header>

        <div class="grid min-h-[38rem] lg:grid-cols-12">
            <aside class="border-b border-[color:rgba(17,24,39,0.18)] bg-[color:rgba(17,24,39,0.02)] lg:col-span-4 lg:border-b-0 lg:border-r">
                <div class="p-5 sm:p-6">
                    @foreach ($categories as $category)
                        @php
                            $categoryKey = (string) ($category['key'] ?? 'general');
                            $isActiveCategory = $activeTool['category_key'] === $categoryKey;
                        @endphp
                        <details class="tool-group mb-3 last:mb-0" @if ($isActiveCategory) open @endif>
                            <summary class="tool-group-summary">
                                <span class="tool-group-head">
                                    <span class="tool-group-icon {{ $categoryIconTone($categoryKey) }}">
                                        {{ $categoryIconLabel($categoryKey) }}
                                    </span>
                                    <span class="tool-group-title">{{ $category['name'] }}</span>
                                </span>
                                <span class="tool-group-meta">
                                    <span class="tool-group-count">{{ count($category['tools']) }}</span>
                                    <span class="tool-group-caret" aria-hidden="true"></span>
                                </span>
                            </summary>

                            <div class="tool-group-body">
                                <ul class="tool-nav-list">
                                    @foreach ($category['tools'] as $tool)
                                        <li>
                                            <a href="{{ route('tools.show', ['slug' => $tool['slug']]) }}" class="tool-nav-link {{ $activeTool['slug'] === $tool['slug'] ? 'is-active' : '' }}">
                                                <span class="tool-nav-icon {{ $toolIconTone($tool['slug']) }}">
                                                    {{ $toolIconLabel($tool) }}
                                                </span>
                                                <span class="tool-nav-name">{{ $tool['name'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </details>
                    @endforeach
                </div>
            </aside>

            <section class="lg:col-span-8">
                <header class="border-b border-[color:rgba(17,24,39,0.18)] px-5 py-5 sm:px-7">
                    <p class="page-kicker">Tools Heading</p>
                    <h2 class="mt-2 font-heading text-3xl text-[color:rgba(17,24,39,0.95)]">{{ $activeTool['name'] }}</h2>
                    <p class="mt-2 text-sm leading-7 muted-copy">{{ $activeTool['tagline'] }}</p>
                </header>

                <div class="space-y-5 px-5 py-6 sm:px-7">
                    <article class="rounded-lg border border-[color:rgba(17,24,39,0.14)] p-4">
                        <p class="text-sm leading-7 muted-copy">{{ $activeTool['description'] }}</p>
                        <p class="mt-4 text-[0.68rem] uppercase tracking-[0.13em] muted-faint">Tool URL</p>
                        <a href="{{ route('tools.show', ['slug' => $activeTool['slug']]) }}" class="mt-1 inline-block break-all text-sm font-semibold text-[var(--accent)] underline decoration-transparent transition hover:decoration-[var(--accent)]">
                            {{ route('tools.show', ['slug' => $activeTool['slug']]) }}
                        </a>
                    </article>

                    <article id="tool-workspace" class="rounded-lg border border-[color:rgba(17,24,39,0.14)] p-4">
                        <h3 class="font-heading text-xl">Process {{ $activeTool['name'] }}</h3>

                        @if ($activeTool['input_type'] === 'text')
                            <form action="{{ route('tools.process', ['slug' => $activeTool['slug']]) }}" method="POST" class="mt-3 space-y-3">
                                @csrf
                                <div>
                                    <label class="form-label" for="text_payload">Input</label>
                                    <textarea id="text_payload" name="text_payload" rows="8" class="form-field @error('text_payload') border-[color:rgba(0,157,49,0.85)] @enderror" placeholder="{{ $textPlaceholder }}">{{ old('text_payload') }}</textarea>
                                    @error('text_payload')
                                        <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="btn-solid">{{ $activeTool['button_label'] }}</button>
                            </form>

                            @if (session('tool_result'))
                                <div class="mt-3 rounded-lg border border-[color:rgba(17,24,39,0.14)] bg-[color:rgba(17,24,39,0.03)] p-4">
                                    <p class="text-[0.68rem] uppercase tracking-[0.13em] muted-faint">{{ session('tool_result_label', 'Result') }}</p>
                                    <pre class="mt-2 overflow-x-auto whitespace-pre-wrap text-sm leading-7 text-[color:rgba(17,24,39,0.9)]">{{ session('tool_result') }}</pre>

                                    @if (str_starts_with((string) session('tool_result'), 'https://'))
                                        <a href="{{ session('tool_result') }}" target="_blank" rel="noopener noreferrer" class="nav-link-sm mt-3 inline-flex">
                                            Open generated link
                                        </a>
                                    @endif
                                </div>
                            @endif
                        @else
                            <form action="{{ route('tools.process', ['slug' => $activeTool['slug']]) }}" method="POST" enctype="multipart/form-data" class="mt-3 space-y-3">
                                @csrf
                                <div>
                                    <label class="form-label" for="upload_file">Upload file</label>
                                    <input id="upload_file" type="file" name="upload_file" class="form-field @error('upload_file') border-[color:rgba(0,157,49,0.85)] @enderror" accept="{{ $activeTool['accept'] }}">
                                    @error('upload_file')
                                        <p class="mt-2 text-sm text-[var(--accent)]">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-2 text-xs muted-faint">Accepted input: {{ $activeTool['accept'] ?: 'File upload' }}</p>
                                </div>

                                <button type="submit" class="btn-solid">{{ $activeTool['button_label'] }}</button>

                                <div>
                                    <label class="form-label" for="processing_note">Notes (optional)</label>
                                    <textarea id="processing_note" name="processing_note" rows="3" class="form-field" placeholder="Add notes for output preferences...">{{ old('processing_note') }}</textarea>
                                </div>
                            </form>
                        @endif
                    </article>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <article class="rounded-lg border border-[color:rgba(17,24,39,0.14)] p-4">
                            <h3 class="font-heading text-xl">All tool details</h3>
                            @if (!empty($activeTool['use_cases']))
                                <ul class="mt-3 space-y-2 text-sm leading-7 text-[color:rgba(17,24,39,0.84)]">
                                    @foreach ($activeTool['use_cases'] as $useCase)
                                        <li class="flex items-start">
                                            <span class="mr-2 mt-[0.08rem] text-[var(--accent)]">+</span>
                                            <span>{{ $useCase }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-3 text-sm muted-copy">Tool details will appear here.</p>
                            @endif
                        </article>

                        <article class="rounded-lg border border-[color:rgba(17,24,39,0.14)] p-4">
                            <h3 class="font-heading text-xl">Actions</h3>
                            <div class="mt-3 flex flex-wrap gap-3">
                                <a href="#tool-workspace" class="btn-solid">Process tool</a>
                                <a href="{{ route('tools.show', ['slug' => $activeTool['slug']]) }}" class="btn-outline">Dedicated URL</a>
                            </div>
                            <p class="mt-4 text-xs uppercase tracking-[0.12em] {{ $activeTool['status'] === 'Live' ? 'text-[var(--accent)]' : 'muted-faint' }}">
                                Status: {{ $activeTool['status'] }}
                            </p>
                        </article>
                    </div>

                    <x-adsense.unit
                        slot="4422776996"
                        format="autorelaxed"
                        style="display:block"
                        wrapperClass="info-card"
                    />
                </div>
            </section>
        </div>
    </section>
@endsection
