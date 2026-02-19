@php
    $selectedTagIds = collect(old('tags', $post->exists ? $post->tags->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)
        ->all();
    $selectedCategoryId = (int) old('category_id', $post->category_id ?: 0);
@endphp

<form
    method="post"
    action="{{ $post->exists ? route('admin.blog.posts.update', $post) : route('admin.blog.posts.store') }}"
    enctype="multipart/form-data"
    class="blog-post-form space-y-6"
>
    @csrf
    @if($post->exists)
        @method('put')
    @endif

    <section class="admin-card p-5">
        <h2 class="font-heading text-xl">Content</h2>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <label for="user_id" class="form-label">Author</label>
                <select id="user_id" name="user_id" class="form-field">
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" @selected((int) old('user_id', $post->user_id ?: auth()->id()) === (int) $author->id)>
                            {{ $author->name }}
                        </option>
                    @endforeach
                </select>
                @error('user_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-field">
                    <option value="published" @selected(old('status', $post->status) === 'published' || old('status', $post->status) === 'scheduled')>Publish</option>
                    <option value="draft" @selected(old('status', $post->status) === 'draft')>Save Draft</option>
                </select>
                @error('status')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-4">
            <label class="inline-flex items-center gap-2 text-sm">
                <input
                    type="checkbox"
                    name="is_featured"
                    value="1"
                    class="h-4 w-4"
                    @checked((bool) old('is_featured', $post->is_featured))
                >
                <span>Mark as featured post</span>
            </label>
            <p class="mt-1 text-xs muted-faint">Only one post stays featured at a time. Selecting this post unfeatures the previous one.</p>
            @error('is_featured')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="title" class="form-label">Title</label>
            <input id="title" type="text" name="title" value="{{ old('title', $post->title) }}" required class="form-field">
            @error('title')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="slug" class="form-label">Slug</label>
            <input id="slug" type="text" name="slug" value="{{ old('slug', $post->slug) }}" class="form-field" placeholder="Auto-generated from title">
            @error('slug')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="published_at" class="form-label">Publish time</label>
            <input
                id="published_at"
                type="datetime-local"
                name="published_at"
                value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}"
                class="form-field"
            >
            <p class="mt-1 text-xs muted-faint">Set a future datetime to auto-schedule while status is Publish.</p>
            @error('published_at')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="excerpt" class="form-label">Excerpt</label>
            <textarea id="excerpt" name="excerpt" rows="3" class="form-field">{{ old('excerpt', $post->excerpt) }}</textarea>
            @error('excerpt')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="body-editor" class="form-label">Body</label>
            <textarea id="body-editor" name="body" rows="16" class="form-field">{{ old('body', $post->body) }}</textarea>
            <p class="mt-1 text-xs muted-faint">
                Supports headings, links, lists, tables, images, and YouTube/Vimeo embeds. Pasting a plain video URL inside its own paragraph converts to an embed.
            </p>
            @error('body')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="admin-card p-5">
        <h2 class="font-heading text-xl">Taxonomy</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <p class="form-label">Category</p>
                <div class="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-[color:rgba(17,24,39,0.18)] bg-[color:rgba(255,255,255,0.95)] p-3">
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent px-3 py-2 hover:border-[color:rgba(0,157,49,0.36)] hover:bg-[color:rgba(0,157,49,0.08)]">
                        <input
                            type="radio"
                            name="category_id"
                            value=""
                            class="peer sr-only"
                            @checked($selectedCategoryId < 1)
                        >
                        <span class="flex-1 text-sm font-medium">None</span>
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-[color:rgba(0,157,49,0.55)] text-[0.72rem] font-bold text-[color:rgba(0,157,49,0.98)] opacity-0 transition peer-checked:opacity-100">&#10003;</span>
                    </label>

                    @foreach($categories as $category)
                        @php $depth = (int) ($category->depth ?? 0); @endphp
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent px-3 py-2 hover:border-[color:rgba(0,157,49,0.36)] hover:bg-[color:rgba(0,157,49,0.08)]">
                            <input
                                type="radio"
                                name="category_id"
                                value="{{ $category->id }}"
                                class="peer sr-only"
                                @checked($selectedCategoryId === (int) $category->id)
                            >
                            <span class="flex-1 text-sm leading-6">{{ str_repeat('-- ', $depth) }}{{ $category->name }}</span>
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-[color:rgba(0,157,49,0.55)] text-[0.72rem] font-bold text-[color:rgba(0,157,49,0.98)] opacity-0 transition peer-checked:opacity-100">&#10003;</span>
                        </label>
                    @endforeach
                </div>
                @error('category_id')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="new_category" class="form-label">New category (optional)</label>
                <input
                    id="new_category"
                    type="text"
                    name="new_category"
                    value="{{ old('new_category') }}"
                    class="form-field"
                    placeholder="Tech > Hosting > VPS"
                >
                <p class="mt-1 text-xs muted-faint">Use `>` or `/` to create nested categories in one go.</p>
                @error('new_category')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-4">
            <p class="form-label">Tags</p>
            <div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border border-[color:rgba(17,24,39,0.18)] bg-[color:rgba(255,255,255,0.95)] p-3">
                @forelse($tags as $tag)
                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-transparent px-3 py-2 hover:border-[color:rgba(0,157,49,0.36)] hover:bg-[color:rgba(0,157,49,0.08)]">
                        <input
                            type="checkbox"
                            name="tags[]"
                            value="{{ $tag->id }}"
                            class="peer sr-only"
                            @checked(in_array((int) $tag->id, $selectedTagIds, true))
                        >
                        <span class="flex-1 text-sm leading-6">{{ $tag->name }}</span>
                        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-[color:rgba(0,157,49,0.55)] text-[0.72rem] font-bold text-[color:rgba(0,157,49,0.98)] opacity-0 transition peer-checked:opacity-100">&#10003;</span>
                    </label>
                @empty
                    <p class="text-sm muted-faint">No tags yet.</p>
                @endforelse
            </div>
            @error('tags')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            @error('tags.*')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4">
            <label for="new_tags" class="form-label">New tags (comma-separated)</label>
            <input id="new_tags" type="text" name="new_tags" value="{{ old('new_tags') }}" class="form-field" placeholder="Laravel, SEO, Deployment">
            @error('new_tags')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="admin-card p-5">
        <h2 class="font-heading text-xl">Images</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <label for="featured_image" class="form-label">Featured image</label>
                <input id="featured_image" type="file" name="featured_image" accept="image/*" class="form-field !p-2">
                <p class="mt-1 text-xs muted-faint">Accepted: JPG, PNG, GIF, WEBP, SVG (max 10 MB).</p>
                @error('featured_image')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                @if($post->featuredImageUrl())
                    <img src="{{ $post->featuredImageUrl() }}" alt="" class="mt-3 h-36 w-full rounded-lg object-cover">
                    <label class="mt-2 inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_featured_image" value="1" class="h-4 w-4">
                        <span>Remove current image</span>
                    </label>
                @endif
            </div>

            <div>
                <label for="featured_image_alt" class="form-label">Featured image alt text</label>
                <input id="featured_image_alt" type="text" name="featured_image_alt" value="{{ old('featured_image_alt', $post->featured_image_alt) }}" class="form-field">
                @error('featured_image_alt')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <label for="og_image" class="form-label">OG image override</label>
                <input id="og_image" type="file" name="og_image" accept="image/*" class="form-field !p-2">
                <p class="mt-1 text-xs muted-faint">Accepted: JPG, PNG, GIF, WEBP, SVG (max 10 MB).</p>
                @error('og_image')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
                @if($post->ogImageUrl())
                    <img src="{{ $post->ogImageUrl() }}" alt="" class="mt-3 h-36 w-full rounded-lg object-cover">
                    <label class="mt-2 inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_og_image" value="1" class="h-4 w-4">
                        <span>Remove OG image</span>
                    </label>
                @endif
            </div>
        </div>
    </section>

    <section class="admin-card p-5">
        <h2 class="font-heading text-xl">SEO</h2>
        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <label for="meta_title" class="form-label">Meta title</label>
                <input id="meta_title" type="text" name="meta_title" value="{{ old('meta_title', $post->meta_title) }}" class="form-field">
                @error('meta_title')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="meta_keywords" class="form-label">Meta keywords</label>
                <input id="meta_keywords" type="text" name="meta_keywords" value="{{ old('meta_keywords', $post->meta_keywords) }}" class="form-field">
                @error('meta_keywords')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-4">
            <label for="meta_description" class="form-label">Meta description</label>
            <textarea id="meta_description" name="meta_description" rows="3" class="form-field">{{ old('meta_description', $post->meta_description) }}</textarea>
            @error('meta_description')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <label for="canonical_url" class="form-label">Canonical URL</label>
                <input id="canonical_url" type="url" name="canonical_url" value="{{ old('canonical_url', $post->canonical_url) }}" class="form-field">
                @error('canonical_url')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="robots" class="form-label">Robots</label>
                <input id="robots" type="text" name="robots" value="{{ old('robots', $post->robots ?: 'index,follow') }}" class="form-field">
                @error('robots')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <div>
                <label for="og_title" class="form-label">OG title override</label>
                <input id="og_title" type="text" name="og_title" value="{{ old('og_title', $post->og_title) }}" class="form-field">
                @error('og_title')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="og_description" class="form-label">OG description override</label>
                <textarea id="og_description" name="og_description" rows="3" class="form-field">{{ old('og_description', $post->og_description) }}</textarea>
                @error('og_description')<p class="mt-1 text-xs text-red-700">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="flex flex-wrap items-center gap-3">
        <button type="submit" class="btn-solid !w-auto !px-5 !py-3 !text-[0.68rem]">Save Post</button>
        @if($post->exists)
            <a href="{{ route('admin.blog.posts.preview', $post) }}" target="_blank" class="btn-outline !w-auto !px-5 !py-3 !text-[0.68rem]">Preview</a>
        @endif
        <a href="{{ route('admin.blog.posts.index') }}" class="nav-link-sm">Back to list</a>
    </div>
</form>

@once
    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.6/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const titleInput = document.getElementById('title');
                const slugInput = document.getElementById('slug');
                const initialSlug = slugInput ? slugInput.value.trim() : '';

                const slugify = (value) => value
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');

                if (titleInput && slugInput) {
                    titleInput.addEventListener('input', function () {
                        if (slugInput.value.trim() !== '' && slugInput.value.trim() !== initialSlug) {
                            return;
                        }

                        slugInput.value = slugify(titleInput.value);
                    });
                }

                if (window.tinymce) {
                    tinymce.init({
                        selector: '#body-editor',
                        menubar: false,
                        height: 520,
                        plugins: 'advlist autolink lists link image table code media',
                        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image media table | code',
                        automatic_uploads: true,
                        image_title: true,
                        images_upload_handler: function (blobInfo, progress) {
                            return new Promise(function (resolve, reject) {
                                const xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route('admin.blog.media.upload') }}');
                                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                                xhr.withCredentials = true;

                                xhr.upload.onprogress = function (event) {
                                    if (event.lengthComputable) {
                                        progress((event.loaded / event.total) * 100);
                                    }
                                };

                                xhr.onload = function () {
                                    if (xhr.status < 200 || xhr.status >= 300) {
                                        reject('Image upload failed: HTTP ' + xhr.status);
                                        return;
                                    }

                                    let json = null;

                                    try {
                                        json = JSON.parse(xhr.responseText);
                                    } catch (error) {
                                        reject('Invalid upload response.');
                                        return;
                                    }

                                    if (!json || typeof json.location !== 'string') {
                                        reject('Upload response missing image URL.');
                                        return;
                                    }

                                    resolve(json.location);
                                };

                                xhr.onerror = function () {
                                    reject('Image upload failed due to network error.');
                                };

                                const formData = new FormData();
                                formData.append('file', blobInfo.blob(), blobInfo.filename());
                                xhr.send(formData);
                            });
                        },
                        content_style: "body { font-family: Montserrat, sans-serif; font-size: 16px; line-height: 1.7; }"
                    });

                    document.querySelectorAll('form.blog-post-form').forEach(function (form) {
                        form.addEventListener('submit', function () {
                            tinymce.triggerSave();
                        });
                    });
                }
            });
        </script>
    @endpush
@endonce
