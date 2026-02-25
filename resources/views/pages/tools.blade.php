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

@if (($activeTool['processor'] ?? '') === 'youtube_downloader')
    @push('head')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('youtube-downloader-form');
                if (!form) {
                    return;
                }

                const input = document.getElementById('youtube_url');
                const loadButton = document.getElementById('youtube-load-btn');
                const clearButton = document.getElementById('youtube-clear-btn');
                const feedback = document.getElementById('youtube-feedback');
                const meta = document.getElementById('youtube-meta');
                const metaTitle = document.getElementById('youtube-meta-title');
                const metaDuration = document.getElementById('youtube-meta-duration');
                const metaThumb = document.getElementById('youtube-meta-thumb');
                const results = document.getElementById('youtube-results');
                const videoOptions = document.getElementById('youtube-video-options');
                const audioOptions = document.getElementById('youtube-audio-options');
                const formatsUrl = form.dataset.formatsUrl;
                const csrfToken = form.querySelector('input[name="_token"]')?.value || '';

                const youtubePattern = /(youtube\.com|youtu\.be)/i;
                const initialLoadLabel = loadButton.textContent;
                let activeController = null;
                let lastResolvedUrl = '';
                let debounceTimer = null;

                const setFeedback = (message, tone = 'info') => {
                    if (!message) {
                        feedback.classList.add('hidden');
                        feedback.textContent = '';
                        feedback.classList.remove('is-error', 'is-success', 'is-info');
                        return;
                    }

                    feedback.classList.remove('hidden');
                    feedback.textContent = message;
                    feedback.classList.remove('is-error', 'is-success', 'is-info');
                    feedback.classList.add(`is-${tone}`);
                };

                const clearRenderedData = () => {
                    meta.classList.add('hidden');
                    results.classList.add('hidden');
                    metaTitle.textContent = '';
                    metaDuration.textContent = '';
                    metaThumb.removeAttribute('src');
                    metaThumb.alt = 'Video thumbnail';
                    metaThumb.classList.add('hidden');
                    videoOptions.innerHTML = '';
                    audioOptions.innerHTML = '';
                };

                const formatDuration = (seconds) => {
                    const total = Number.isFinite(seconds) ? Math.max(0, Math.floor(seconds)) : 0;
                    if (total <= 0) {
                        return 'Duration unavailable';
                    }

                    const hours = Math.floor(total / 3600);
                    const minutes = Math.floor((total % 3600) / 60);
                    const secs = total % 60;

                    if (hours > 0) {
                        return `${hours}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                    }

                    return `${minutes}:${String(secs).padStart(2, '0')}`;
                };

                const renderOptionRows = (container, options) => {
                    container.innerHTML = '';

                    if (!Array.isArray(options) || options.length === 0) {
                        const empty = document.createElement('p');
                        empty.className = 'text-sm muted-copy';
                        empty.textContent = 'No options found for this media.';
                        container.appendChild(empty);
                        return;
                    }

                    options.forEach((option) => {
                        const row = document.createElement('div');
                        row.className = 'yt-option-row';

                        const label = document.createElement('span');
                        label.className = 'yt-option-label';
                        label.textContent = `${option.label || 'Unknown quality'} -`;

                        const link = document.createElement('a');
                        link.className = 'btn-solid yt-option-download';
                        link.href = option.download_url || '#';
                        link.rel = 'nofollow';
                        link.setAttribute('download', '');
                        link.textContent = 'Download';

                        row.appendChild(label);
                        row.appendChild(link);
                        container.appendChild(row);
                    });
                };

                const setLoading = (loading) => {
                    loadButton.disabled = loading;
                    loadButton.textContent = loading ? 'Loading formats...' : initialLoadLabel;
                };

                clearRenderedData();
                setFeedback('', 'info');

                const requestFormats = async (force = false) => {
                    const candidateUrl = input.value.trim();
                    if (!candidateUrl) {
                        clearRenderedData();
                        setFeedback('', 'info');
                        return;
                    }

                    if (!force && !youtubePattern.test(candidateUrl)) {
                        return;
                    }

                    if (!force && candidateUrl === lastResolvedUrl) {
                        return;
                    }

                    if (activeController) {
                        activeController.abort();
                    }

                    const requestController = new AbortController();
                    activeController = requestController;
                    setLoading(true);
                    setFeedback('Loading available formats...', 'info');

                    try {
                        const response = await fetch(formatsUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new URLSearchParams({
                                url: candidateUrl,
                            }),
                            signal: requestController.signal,
                        });

                        let payload = null;
                        try {
                            payload = await response.json();
                        } catch (jsonError) {
                            payload = null;
                        }

                        if (!response.ok || !payload?.ok) {
                            throw new Error(payload?.message || 'Unable to load format options for this URL.');
                        }

                        const normalizedUrl = payload.normalized_url || candidateUrl;
                        lastResolvedUrl = normalizedUrl;
                        input.value = normalizedUrl;

                        metaTitle.textContent = payload.title || 'YouTube video';
                        metaDuration.textContent = `Duration: ${formatDuration(payload.duration)}`;

                        if (payload.thumbnail) {
                            metaThumb.src = payload.thumbnail;
                            metaThumb.alt = `Thumbnail for ${payload.title || 'YouTube video'}`;
                            metaThumb.classList.remove('hidden');
                        } else {
                            metaThumb.removeAttribute('src');
                            metaThumb.alt = 'Video thumbnail';
                            metaThumb.classList.add('hidden');
                        }

                        renderOptionRows(videoOptions, payload.video_options || []);
                        renderOptionRows(audioOptions, payload.audio_options || []);

                        meta.classList.remove('hidden');
                        results.classList.remove('hidden');
                        setFeedback('Formats generated. Choose a size and click Download.', 'success');
                    } catch (error) {
                        if (error?.name === 'AbortError') {
                            return;
                        }

                        clearRenderedData();
                        setFeedback(error?.message || 'Failed to load formats for this URL.', 'error');
                    } finally {
                        if (activeController === requestController) {
                            activeController = null;
                            setLoading(false);
                        }
                    }
                };

                form.addEventListener('submit', (event) => {
                    event.preventDefault();
                    requestFormats(true);
                });

                input.addEventListener('paste', () => {
                    window.setTimeout(() => requestFormats(true), 80);
                });

                input.addEventListener('input', () => {
                    window.clearTimeout(debounceTimer);
                    debounceTimer = window.setTimeout(() => requestFormats(false), 650);
                });

                input.addEventListener('blur', () => {
                    requestFormats(false);
                });

                clearButton.addEventListener('click', () => {
                    if (activeController) {
                        activeController.abort();
                        activeController = null;
                    }

                    window.clearTimeout(debounceTimer);
                    input.value = '';
                    lastResolvedUrl = '';
                    clearRenderedData();
                    setFeedback('', 'info');
                    setLoading(false);
                });
            });
        </script>
    @endpush
@endif

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
                            @if (($activeTool['processor'] ?? '') === 'youtube_downloader')
                                <form id="youtube-downloader-form" class="mt-3 space-y-3" data-formats-url="{{ route('tools.formats', ['slug' => $activeTool['slug']]) }}">
                                    @csrf
                                    <div>
                                        <label class="form-label" for="youtube_url">YouTube URL</label>
                                        <input
                                            id="youtube_url"
                                            name="youtube_url"
                                            type="url"
                                            class="form-field"
                                            placeholder="https://www.youtube.com/watch?v=..."
                                            inputmode="url"
                                            autocomplete="off"
                                            spellcheck="false"
                                        >
                                        <p class="mt-2 text-xs muted-faint">Paste a YouTube link. Formats load automatically, then click Download on the quality you want.</p>
                                    </div>

                                    <div class="flex flex-wrap gap-3">
                                        <button id="youtube-load-btn" type="submit" class="btn-solid sm:w-auto">Load formats</button>
                                        <button id="youtube-clear-btn" type="button" class="btn-outline sm:w-auto">Clear</button>
                                    </div>
                                </form>

                                <div id="youtube-feedback" class="yt-feedback mt-3 hidden"></div>

                                <article id="youtube-meta" class="yt-meta mt-3 hidden">
                                    <img id="youtube-meta-thumb" class="yt-meta-thumb" alt="Video thumbnail">
                                    <div class="yt-meta-content">
                                        <p class="text-[0.68rem] uppercase tracking-[0.13em] muted-faint">Detected video</p>
                                        <h4 id="youtube-meta-title" class="mt-2 font-heading text-lg text-[color:rgba(17,24,39,0.95)]"></h4>
                                        <p id="youtube-meta-duration" class="mt-1 text-xs uppercase tracking-[0.11em] muted-faint"></p>
                                    </div>
                                </article>

                                <article id="youtube-results" class="yt-results mt-3 hidden">
                                    <section class="yt-results-block">
                                        <h4 class="font-heading text-lg">Video versions</h4>
                                        <div id="youtube-video-options" class="yt-options-list mt-3"></div>
                                    </section>

                                    <section class="yt-results-block">
                                        <h4 class="font-heading text-lg">MP3 versions</h4>
                                        <div id="youtube-audio-options" class="yt-options-list mt-3"></div>
                                    </section>
                                </article>
                            @else
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
